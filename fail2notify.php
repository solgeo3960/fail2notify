<?php
/**
 * Plugin Name: Fail2Notify — Mail Failure Alerts
 * Description: Detect wp_mail() transport failures and send instant, masked Slack notifications so you never miss email issues.
 * Version: 1.0.0
 * Author: Solgeo Corp.
 * Author URI: https://solgeo.co.jp/
 * License: GPLv2 or later
 * Text Domain: fail2notify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FAIL2NOTIFY_VERSION', '1.0.0' );
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
				esc_html_e( 'Fail2Notify is in Composer mode. Please run composer install before activating the plugin.', 'fail2notify' );
				echo '</p></div>';
			}
		}
	);
	return;
}

require_once $fail2notify_autoload;

$fail2notify_config = new \F2N\Core\Config(
	[
		'slug'              => 'fail2notify',
		'option_key'        => FAIL2NOTIFY_OPTION_KEY,
		'log_option_key'    => FAIL2NOTIFY_LOG_OPTION_KEY,
		'settings_page_slug'=> 'fail2notify-settings',
		'text_domain'       => 'fail2notify',
		'menu_page_title'   => 'Fail2Notify Settings',
		'menu_title'        => 'Fail2Notify',
		'log_limit'         => 20,
	]
);

$fail2notify_logger = new \F2N\Core\Logger( $fail2notify_config );
$fail2notify_plugin = new \F2N\Core\Plugin( $fail2notify_config, $fail2notify_logger );
$fail2notify_admin  = new \F2N\Core\Admin( $fail2notify_config, $fail2notify_logger );

add_filter(
	$fail2notify_config->hook( 'notifiers' ),
	static function ( array $notifiers, array $settings ) {
		if ( ! empty( $settings['slack_webhook'] ) ) {
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
		$fail2notify_plugin->init();
		$fail2notify_admin->init();
	}
);
