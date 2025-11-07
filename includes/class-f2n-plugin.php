<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class F2N_Plugin {

    public function init() {
        // 送信失敗フック（全フォーム共通で拾える）
        add_action( 'wp_mail_failed', [ $this, 'handle_mail_failed' ], 10, 1 );
        // 手動テスト用（管理画面から POST）
        add_action( 'admin_post_f2n_send_test', [ $this, 'handle_send_test' ] );
    }

    public function handle_mail_failed( $wp_error ) {
        $settings = get_option( F2N_OPTION_KEY, [] );

        // OFF の場合は何もしない
        if ( empty( $settings['enabled'] ) ) return;

        $data = $wp_error->get_error_data(); // phpmailerException 由来の配列が入ることが多い
        $error_messages = $wp_error->get_error_messages();

        $payload = $this->build_payload_from_error( $data, $error_messages, $settings );

        // ログ保存（直近50件）
        F2N_Logger::push( $payload );

        // 通知（MVPはSlackのみ）
        if ( ! empty( $settings['slack_webhook'] ) ) {
            $notifier = new F2N_Slack_Notifier( $settings['slack_webhook'] );
            $notifier->notify( $payload );
        }
    }

    private function build_payload_from_error( $data, $error_messages, $settings ) {
        $site   = !empty($settings['site_label']) ? $settings['site_label'] : get_bloginfo('name');
        $env    = !empty($settings['env_label'])  ? $settings['env_label']  : '';
        $when   = current_time( 'mysql' );

        // エラーメッセージを結合
        $err_text = is_array( $error_messages ) ? implode( " | ", $error_messages ) : (string) $error_messages;

        // 送信データの抽出（PHPMailerの配列を想定）
        $to      = $data['to']      ?? '';
        $subject = $data['subject'] ?? '';
        $headers = $data['headers'] ?? '';
        $body    = $data['body']    ?? '';

        // 簡易マスキング
        $mask = function( $text ) {
            if ( is_array($text) ) $text = implode(', ', $text);
            $text = (string) $text;
            return preg_replace_callback(
                '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',
                function($m){
                    $parts = explode('@', $m[0]);
                    $name  = $parts[0];
                    $masked = substr($name,0,1) . str_repeat('*', max(0, strlen($name)-2)) . substr($name,-1);
                    return $masked.'@'.$parts[1];
                },
                $text
            );
        };

        // メール送信元のURLを取得（リファラー優先、なければ現在のリクエストURL）
        $referer_url = '';
        if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
            $referer_url = esc_url_raw( $_SERVER['HTTP_REFERER'] );
        } elseif ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
            $referer_url = home_url( $_SERVER['REQUEST_URI'] );
        }
        
        // サイトURLを取得
        $site_url = home_url();

        $payload = [
            'site'      => $site,
            'env'       => $env,
            'datetime'  => $when,
            'error'     => $err_text,
            'to'        => $mask( $to ),
            'subject'   => $mask( $subject ),
            'headers'   => $mask( $headers ),
            'body'      => mb_strimwidth( $mask( $body ), 0, 1200, '…', 'UTF-8' ), // 長文はカット
            'url'       => $referer_url,      // メール送信元のURL
            'site_url'  => $site_url,         // サイトURL
        ];
        return $payload;
    }

    public function handle_send_test() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
        check_admin_referer( 'f2n_send_test' );

        $settings = get_option( F2N_OPTION_KEY, [] );
        $payload = [
            'site'      => $settings['site_label'] ?? get_bloginfo('name'),
            'env'       => $settings['env_label'] ?? '',
            'datetime'  => current_time( 'mysql' ),
            'error'     => 'This is a test message from Fail2Notify.',
            'to'        => 'test@example.com',
            'subject'   => 'Fail2Notify Test',
            'headers'   => '',
            'body'      => 'If you see this in Slack, your webhook works.',
            'url'       => admin_url( 'options-general.php?page=f2n-settings' ),
            'site_url'  => home_url(),
        ];

        F2N_Logger::push( $payload );

        if ( ! empty( $settings['slack_webhook'] ) ) {
            $notifier = new F2N_Slack_Notifier( $settings['slack_webhook'] );
            $notifier->notify( $payload );
        }

        wp_redirect( admin_url( 'options-general.php?page=f2n-settings&f2n_test=1' ) );
        exit;
    }
}
