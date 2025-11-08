<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class F2N_Plugin {

    public const TEST_NOTICE_ACTION = 'f2n_test_notice';

    public function init() {
        // Listen for wp_mail failures so we can capture any form/plugin that relies on wp_mail().
        add_action( 'wp_mail_failed', [ $this, 'handle_mail_failed' ], 10, 1 );
        // Handle manual test submissions triggered from the settings page.
        add_action( 'admin_post_f2n_send_test', [ $this, 'handle_send_test' ] );
    }

    public function handle_mail_failed( $wp_error ) {
        $settings = get_option( F2N_OPTION_KEY, [] );

        // If monitoring is disabled there is nothing to do.
        if ( empty( $settings['enabled'] ) ) {
            return;
        }

        $data = $wp_error->get_error_data(); // Often contains PHPMailer payload details.
        $error_messages = $wp_error->get_error_messages();

        $payload = $this->build_payload_from_error( $data, $error_messages, $settings );

        // Store the last 50 failures so admins can inspect them later.
        F2N_Logger::push( $payload );

        // For v1 Slack webhook notifications are the only built-in transport.
        if ( ! empty( $settings['slack_webhook'] ) ) {
            $notifier = new F2N_Slack_Notifier( $settings['slack_webhook'] );
            $notifier->notify( $payload );
        }
    }

    private function build_payload_from_error( $data, $error_messages, $settings ) {
        $site   = ! empty( $settings['site_label'] ) ? $settings['site_label'] : get_bloginfo( 'name' );
        $env    = ! empty( $settings['env_label'] )  ? $settings['env_label']  : '';
        $when   = current_time( 'mysql' );

        // Combine all error messages into a single string.
        $err_text = is_array( $error_messages ) ? implode( ' | ', $error_messages ) : (string) $error_messages;

        // Pull PHPMailer context when available.
        $to      = $data['to']      ?? '';
        $subject = $data['subject'] ?? '';
        $headers = $data['headers'] ?? '';
        $body    = $data['body']    ?? '';

        // Lightweight masking for any email addresses that may appear in the payload.
        $mask = function ( $text ) {
            if ( is_array( $text ) ) {
                $text = implode( ', ', $text );
            }

            $text = (string) $text;

            return preg_replace_callback(
                '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',
                function ( $matches ) {
                    $parts  = explode( '@', $matches[0] );
                    $name   = $parts[0];
                    $masked = substr( $name, 0, 1 ) . str_repeat( '*', max( 0, strlen( $name ) - 2 ) ) . substr( $name, -1 );

                    return $masked . '@' . $parts[1];
                },
                $text
            );
        };

        // Determine the origin URL (referer preferred, fallback to the current request).
        $referer_url = '';

        if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
            $referer_raw = sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
            $referer_url = esc_url_raw( $referer_raw );
        } elseif ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
            $request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
            $referer_url = esc_url_raw( home_url( $request_uri ) );
        }

        // Include the base site URL for quick identification inside Slack.
        $site_url = home_url();

        $payload = [
            'site'     => $site,
            'env'      => $env,
            'datetime' => $when,
            'error'    => $err_text,
            'to'       => $mask( $to ),
            'subject'  => $mask( $subject ),
            'headers'  => $mask( $headers ),
            'body'     => mb_strimwidth( $mask( $body ), 0, 1200, '...', 'UTF-8' ), // Keep Slack threads readable.
            'url'      => $referer_url,
            'site_url' => $site_url,
        ];

        return $payload;
    }

    public function handle_send_test() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Forbidden' );
        }

        check_admin_referer( 'f2n_send_test' );

        $settings = get_option( F2N_OPTION_KEY, [] );

        $payload = [
            'site'     => $settings['site_label'] ?? get_bloginfo( 'name' ),
            'env'      => $settings['env_label'] ?? '',
            'datetime' => current_time( 'mysql' ),
            'error'    => 'This is a test message from Fail2Notify.',
            'to'       => 'test@example.com',
            'subject'  => 'Fail2Notify Test',
            'headers'  => '',
            'body'     => 'If you see this in Slack, your webhook works.',
            'url'      => admin_url( 'options-general.php?page=f2n-settings' ),
            'site_url' => home_url(),
        ];

        F2N_Logger::push( $payload );

        if ( ! empty( $settings['slack_webhook'] ) ) {
            $notifier = new F2N_Slack_Notifier( $settings['slack_webhook'] );
            $notifier->notify( $payload );
        }

        $redirect_url = add_query_arg(
            [
                'page'     => 'f2n-settings',
                'f2n_test' => wp_create_nonce( self::TEST_NOTICE_ACTION ),
            ],
            admin_url( 'options-general.php' )
        );

        wp_safe_redirect( $redirect_url );
        exit;
    }
}