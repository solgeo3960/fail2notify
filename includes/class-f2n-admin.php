<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class F2N_Admin {

    public function init() {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function add_menu() {
        add_options_page(
            'Fail2Notify',
            'Fail2Notify',
            'manage_options',
            'f2n-settings',
            [ $this, 'render_page' ]
        );
    }

    public function register_settings() {
        register_setting( 'f2n_settings_group', F2N_OPTION_KEY, [ $this, 'sanitize' ] );

        add_settings_section(
            'f2n_main',
            __( 'General Settings', 'fail2notify' ),
            function() {
                echo '<p>' . esc_html__( 'Monitor wp_mail() failures and send alerts to Slack.', 'fail2notify' ) . '</p>';
            },
            'f2n-settings'
        );

        add_settings_field(
            'enabled',
            __( 'Enable Monitoring', 'fail2notify' ),
            function() {
                $opts = get_option( F2N_OPTION_KEY, [] );
                ?>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr( F2N_OPTION_KEY ); ?>[enabled]" value="1" <?php checked( ! empty( $opts['enabled'] ) ); ?>>
                    <?php esc_html_e( 'Turn on failure detection', 'fail2notify' ); ?>
                </label>
                <?php
            },
            'f2n-settings',
            'f2n_main'
        );

        add_settings_field(
            'site_label',
            __( 'Site Label For Notifications', 'fail2notify' ),
            function() {
                $opts = get_option( F2N_OPTION_KEY, [] );
                $val  = $opts['site_label'] ?? get_bloginfo( 'name' );
                echo '<input type="text" class="regular-text" name="' . esc_attr( F2N_OPTION_KEY ) . '[site_label]" value="' . esc_attr( $val ) . '">';
            },
            'f2n-settings',
            'f2n_main'
        );

        add_settings_field(
            'env_label',
            __( 'Environment Label (e.g. PROD / STG)', 'fail2notify' ),
            function() {
                $opts = get_option( F2N_OPTION_KEY, [] );
                $val  = $opts['env_label'] ?? '';
                echo '<input type="text" class="regular-text" name="' . esc_attr( F2N_OPTION_KEY ) . '[env_label]" value="' . esc_attr( $val ) . '">';
            },
            'f2n-settings',
            'f2n_main'
        );

        add_settings_field(
            'slack_webhook',
            __( 'Slack Webhook URL', 'fail2notify' ),
            function() {
                $opts = get_option( F2N_OPTION_KEY, [] );
                $val  = $opts['slack_webhook'] ?? '';
                echo '<input type="url" class="regular-text code" placeholder="https://hooks.slack.com/services/XXXXX/XXXXX/XXXXX" name="' . esc_attr( F2N_OPTION_KEY ) . '[slack_webhook]" value="' . esc_attr( $val ) . '">';
                echo '<p class="description">' . esc_html__( 'Create a Slack app at api.slack.com/apps, enable “Incoming Webhooks”, add the target channel, then copy the generated webhook URL into this field.', 'fail2notify' ) . '</p>';
            },
            'f2n-settings',
            'f2n_main'
        );
    }

    public function sanitize( $input ) {
        $out = [];
        $out['enabled']       = empty( $input['enabled'] ) ? 0 : 1;
        $out['site_label']    = sanitize_text_field( $input['site_label'] ?? '' );
        $out['env_label']     = sanitize_text_field( $input['env_label'] ?? '' );
        $out['slack_webhook'] = esc_url_raw( $input['slack_webhook'] ?? '' );
        return $out;
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $opts = get_option( F2N_OPTION_KEY, [] );
        $logs = F2N_Logger::get_all();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Fail2Notify Settings', 'fail2notify' ); ?></h1>
            <?php
            $test_notice = '';
            if ( isset( $_GET['f2n_test'] ) ) {
                $test_notice = sanitize_key( wp_unslash( $_GET['f2n_test'] ) );
            }
            if ( $test_notice && wp_verify_nonce( $test_notice, F2N_Plugin::TEST_NOTICE_ACTION ) ) :
                ?>
                <div class="notice notice-success"><p><?php esc_html_e( 'Test notification sent.', 'fail2notify' ); ?></p></div>
            <?php endif; ?>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'f2n_settings_group' );
                do_settings_sections( 'f2n-settings' );
                submit_button();
                ?>
            </form>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'f2n_send_test' ); ?>
                <input type="hidden" name="action" value="f2n_send_test">
                <?php submit_button( __( 'Send Test Notification', 'fail2notify' ), 'secondary' ); ?>
            </form>

            <h2><?php esc_html_e( 'Recent Failure Logs', 'fail2notify' ); ?></h2>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'fail2notify' ); ?></th>
                    <th><?php esc_html_e( 'Environment', 'fail2notify' ); ?></th>
                    <th><?php esc_html_e( 'Recipient', 'fail2notify' ); ?></th>
                    <th><?php esc_html_e( 'Subject', 'fail2notify' ); ?></th>
                    <th><?php esc_html_e( 'Error', 'fail2notify' ); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if ( empty( $logs ) ) : ?>
                    <tr><td colspan="5"><?php esc_html_e( 'No logs yet.', 'fail2notify' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( array_reverse( $logs ) as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html( $row['datetime'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $row['env'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $row['to'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $row['subject'] ?? '' ); ?></td>
                            <td><?php echo esc_html( mb_strimwidth( $row['error'] ?? '', 0, 100, '...', 'UTF-8' ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
