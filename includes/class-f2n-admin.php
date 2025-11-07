<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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

        add_settings_section( 'f2n_main', '基本設定', function() {
            echo '<p>wp_mail() の失敗を検知して通知します。まず Slack Webhook を設定してください。</p>';
        }, 'f2n-settings' );

        add_settings_field( 'enabled', '有効化', function(){
            $opts = get_option( F2N_OPTION_KEY, [] );
            ?>
            <label><input type="checkbox" name="<?php echo esc_attr(F2N_OPTION_KEY); ?>[enabled]" value="1" <?php checked( ! empty($opts['enabled']) ); ?>> 送信失敗の監視を有効化</label>
            <?php
        }, 'f2n-settings', 'f2n_main' );

        add_settings_field( 'site_label', 'サイト名（通知用）', function(){
            $opts = get_option( F2N_OPTION_KEY, [] );
            $val  = $opts['site_label'] ?? get_bloginfo('name');
            echo '<input type="text" class="regular-text" name="'.esc_attr(F2N_OPTION_KEY).'[site_label]" value="'.esc_attr($val).'">';
        }, 'f2n-settings', 'f2n_main' );

        add_settings_field( 'env_label', '環境ラベル（例：PROD / STG）', function(){
            $opts = get_option( F2N_OPTION_KEY, [] );
            $val  = $opts['env_label'] ?? '';
            echo '<input type="text" class="regular-text" name="'.esc_attr(F2N_OPTION_KEY).'[env_label]" value="'.esc_attr($val).'">';
        }, 'f2n-settings', 'f2n_main' );

        add_settings_field( 'slack_webhook', 'Slack Webhook URL', function(){
            $opts = get_option( F2N_OPTION_KEY, [] );
            $val  = $opts['slack_webhook'] ?? '';
            echo '<input type="url" class="regular-text code" placeholder="https://hooks.slack.com/services/XXXXX/XXXXX/XXXXX" name="'.esc_attr(F2N_OPTION_KEY).'[slack_webhook]" value="'.esc_attr($val).'">';
        }, 'f2n-settings', 'f2n_main' );
    }

    public function sanitize( $input ) {
        $out = [];
        $out['enabled']       = empty($input['enabled']) ? 0 : 1;
        $out['site_label']    = sanitize_text_field( $input['site_label'] ?? '' );
        $out['env_label']     = sanitize_text_field( $input['env_label']  ?? '' );
        $out['slack_webhook'] = esc_url_raw( $input['slack_webhook'] ?? '' );
        return $out;
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $opts = get_option( F2N_OPTION_KEY, [] );
        $logs = F2N_Logger::get_all();
        ?>
        <div class="wrap">
            <h1>Fail2Notify 設定</h1>
            <?php if ( isset($_GET['f2n_test']) ) : ?>
                <div class="notice notice-success"><p>テスト通知を送信しました。</p></div>
            <?php endif; ?>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'f2n_settings_group' );
                do_settings_sections( 'f2n-settings' );
                submit_button();
                ?>
            </form>

            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                <?php wp_nonce_field( 'f2n_send_test' ); ?>
                <input type="hidden" name="action" value="f2n_send_test">
                <?php submit_button( 'テスト通知を送る', 'secondary' ); ?>
            </form>

            <h2>直近の失敗ログ</h2>
            <table class="widefat striped">
                <thead><tr>
                    <th>日時</th><th>環境</th><th>宛先</th><th>件名</th><th>エラー</th>
                </tr></thead>
                <tbody>
                <?php if ( empty($logs) ) : ?>
                    <tr><td colspan="5">まだログはありません。</td></tr>
                <?php else:
                    foreach ( array_reverse($logs) as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html($row['datetime'] ?? ''); ?></td>
                            <td><?php echo esc_html($row['env'] ?? ''); ?></td>
                            <td><?php echo esc_html($row['to'] ?? ''); ?></td>
                            <td><?php echo esc_html($row['subject'] ?? ''); ?></td>
                            <td><?php echo esc_html(mb_strimwidth($row['error'] ?? '',0,100,'…','UTF-8')); ?></td>
                        </tr>
                    <?php endforeach;
                endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
