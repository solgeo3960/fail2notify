<?php
/**
 * Plugin Name: Fail2Notify – WP Mail Failure Alerts
 * Description: wp_mail() の送信失敗を検知して Slack などへ即時通知します（MVPはSlack対応）。
 * Version: 0.1.0
 * Author: Solgeo Corp.
 * Text Domain: fail2notify
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'F2N_VERSION', '0.1.0' );
define( 'F2N_PATH', plugin_dir_path( __FILE__ ) );
define( 'F2N_URL',  plugin_dir_url( __FILE__ ) );
define( 'F2N_OPTION_KEY', 'f2n_settings' );
define( 'F2N_LOG_OPTION_KEY', 'f2n_logs' ); // シンプルなオプション保存（将来はCPTや独自テーブルに移行可）

require_once F2N_PATH . 'includes/class-f2n-plugin.php';
require_once F2N_PATH . 'includes/class-f2n-admin.php';
require_once F2N_PATH . 'includes/class-f2n-logger.php';
require_once F2N_PATH . 'includes/notifiers/interface-f2n-notifier.php';
require_once F2N_PATH . 'includes/notifiers/class-f2n-slack.php';

add_action( 'plugins_loaded', function () {
    ( new F2N_Plugin() )->init();
    ( new F2N_Admin() )->init();
} );

