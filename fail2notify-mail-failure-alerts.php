<?php
/**
 * Plugin Name: Fail2Notify — Mail Failure Alerts
 * Description: Detect wp_mail() transport failures and send instant, masked Slack notifications so you never miss email issues.
 * Version: 1.0.2
 * Author: Solgeo Corp.
 * Author URI: https://solgeo.co.jp/
 * License: GPLv2 or later
 * Text Domain: fail2notify-mail-failure-alerts
 *
 * @package Fail2Notify\MailFailureAlerts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FAIL2NOTIFY_VERSION', '1.0.2' );
define( 'FAIL2NOTIFY_PATH', plugin_dir_path( __FILE__ ) );
define( 'FAIL2NOTIFY_URL', plugin_dir_url( __FILE__ ) );
define( 'FAIL2NOTIFY_OPTION_KEY', 'fail2notify_settings' );
define( 'FAIL2NOTIFY_LOG_OPTION_KEY', 'fail2notify_logs' );

$fail2notify_autoload = __DIR__ . '/vendor/autoload.php';

if ( ! file_exists( $fail2notify_autoload ) ) {
	add_action(
		'admin_notices',
		static function () {
			if ( current_user_can( 'manage_options' ) ) {
				echo '<div class="notice notice-error"><p>';
				esc_html_e( 'Fail2Notify is in Composer mode. Please run composer install before activating the plugin.', 'fail2notify-mail-failure-alerts' );
				echo '</p></div>';
			}
		}
	);
	return;
}

require_once $fail2notify_autoload;

$fail2notify_config = new \F2N\Core\Config(
	array(
		'slug'               => 'fail2notify-mail-failure-alerts',
		'option_key'         => FAIL2NOTIFY_OPTION_KEY,
		'log_option_key'     => FAIL2NOTIFY_LOG_OPTION_KEY,
		'settings_page_slug' => 'fail2notify-settings',
		'text_domain'        => 'fail2notify-mail-failure-alerts',
		'menu_page_title'    => 'Fail2Notify Settings',
		'menu_title'         => 'Fail2Notify',
		'log_limit'          => 20,
	)
);

$fail2notify_logger = new \F2N\Core\Logger( $fail2notify_config );
$fail2notify_plugin = new \F2N\Core\Plugin( $fail2notify_config, $fail2notify_logger );
$fail2notify_admin  = new \F2N\Core\Admin( $fail2notify_config, $fail2notify_logger );

add_filter(
	$fail2notify_config->hook( 'settings_fields' ),
	static function ( array $fields ) use ( $fail2notify_config ) {
		// Modify existing Slack field to match Chatwork style.
		foreach ( $fields as &$field ) {
			if ( 'slack_webhook' === $field['id'] ) {
				$field['title']    = __( 'Slack Notifications', 'fail2notify-mail-failure-alerts' );
				$field['callback'] = static function () use ( $fail2notify_config ) {
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName -- Config exposes camelCase keys.
					$option_key    = $fail2notify_config->optionKey;
					$opts          = get_option( $option_key, array() );
					$slack_enabled = ! isset( $opts['slack_enabled'] ) || ! empty( $opts['slack_enabled'] );
					$val           = isset( $opts['slack_webhook'] ) ? $opts['slack_webhook'] : '';
					?>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $option_key ); ?>[slack_enabled]" value="1" <?php checked( $slack_enabled ); ?>>
						<?php esc_html_e( 'Enable Slack alerts', 'fail2notify-mail-failure-alerts' ); ?>
					</label>
					<br><br>
					<label>
						<?php esc_html_e( 'Webhook URL:', 'fail2notify-mail-failure-alerts' ); ?>
						<input type="url" class="regular-text code" placeholder="https://hooks.slack.com/services/..." name="<?php echo esc_attr( $option_key ); ?>[slack_webhook]" value="<?php echo esc_attr( $val ); ?>">
					</label>
					<p class="description"><?php esc_html_e( 'Create an incoming webhook inside Slack and paste the URL here.', 'fail2notify-mail-failure-alerts' ); ?></p>
					<?php
				};
				break;
			}
		}
		unset( $field );

		return $fields;
	}
);

add_filter(
	$fail2notify_config->hook( 'sanitize_settings' ),
	static function ( array $sanitized, array $input ) {
		$sanitized['slack_enabled'] = empty( $input['slack_enabled'] ) ? 0 : 1;
		return $sanitized;
	},
	10,
	2
);

add_filter(
	$fail2notify_config->hook( 'notifiers' ),
	static function ( array $notifiers, array $settings ) {
		$slack_enabled = ! isset( $settings['slack_enabled'] ) || ! empty( $settings['slack_enabled'] );
		if ( $slack_enabled && ! empty( $settings['slack_webhook'] ) ) {
			$notifiers[] = new \F2N\Core\Notifiers\Slack( $settings['slack_webhook'] );
		}

		return $notifiers;
	},
	10,
	2
);

add_action(
	'plugins_loaded',
	static function () use ( $fail2notify_plugin, $fail2notify_admin ) {
		load_plugin_textdomain( 'fail2notify-mail-failure-alerts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		$fail2notify_plugin->init();
		$fail2notify_admin->init();
	}
);
