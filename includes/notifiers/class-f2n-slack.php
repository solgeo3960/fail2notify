<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class F2N_Slack_Notifier implements F2N_Notifier_Interface {

    private $webhook;

    public function __construct( string $webhook ) {
        $this->webhook = $webhook;
    }

    public function notify( array $payload ): bool {
        if ( empty( $this->webhook ) ) return false;

        $title = sprintf(
            '📮 WPメール送信失敗 %s%s',
            ! empty($payload['site']) ? " – {$payload['site']}" : '',
            ! empty($payload['env'])  ? " [{$payload['env']}]" : ''
        );

        $lines = [
            "*日時*: {$payload['datetime']}",
            "*宛先*: {$payload['to']}",
            "*件名*: {$payload['subject']}",
            "*エラー*: {$payload['error']}",
        ];
        
        // サイト名とサイトURL
        if ( ! empty( $payload['site'] ) && ! empty( $payload['site_url'] ) ) {
            $lines[] = "*サイト*: <{$payload['site_url']}|{$payload['site']}>";
        } elseif ( ! empty( $payload['site'] ) ) {
            $lines[] = "*サイト*: {$payload['site']}";
        }
        
        // メール送信元のURL
        if ( ! empty( $payload['url'] ) ) {
            $lines[] = "*送信元URL*: <{$payload['url']}|{$payload['url']}>";
        }
        
        if ( ! empty( $payload['body'] ) ) {
            $lines[] = "*本文(一部)*:\n```" . $this->truncate_for_codeblock( $payload['body'] ) . "```";
        }

        $text = $title . "\n" . implode("\n", $lines);

        $res = wp_remote_post( $this->webhook, [
            'headers' => [ 'Content-Type' => 'application/json; charset=utf-8' ],
            'body'    => wp_json_encode([ 'text' => $text ]),
            'timeout' => 8,
        ] );

        if ( is_wp_error($res) ) return false;
        $code = wp_remote_retrieve_response_code( $res );
        return $code >= 200 && $code < 300;
    }

    private function truncate_for_codeblock( string $body ): string {
        // Slackコードブロック内の過剰長を抑える
        if ( mb_strlen($body, 'UTF-8') > 900 ) {
            return mb_substr( $body, 0, 900, 'UTF-8' ) . '…';
        }
        return $body;
    }
}
