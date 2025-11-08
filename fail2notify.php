<?php
/**
 * Plugin Name: Fail2Notify - WP Mail Failure Alerts
 * Plugin URI: https://solgeo.co.jp/
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

define( 'F2N_VERSION', '1.0.0' );
define( 'F2N_PATH', plugin_dir_path( __FILE__ ) );
define( 'F2N_URL', plugin_dir_url( __FILE__ ) );
define( 'F2N_OPTION_KEY', 'f2n_settings' );
define( 'F2N_LOG_OPTION_KEY', 'f2n_logs' ); // Simple option storage; migrate to CPT/DB later if needed.

require_once F2N_PATH . 'includes/class-f2n-plugin.php';
require_once F2N_PATH . 'includes/class-f2n-admin.php';
require_once F2N_PATH . 'includes/class-f2n-logger.php';
require_once F2N_PATH . 'includes/notifiers/interface-f2n-notifier.php';
require_once F2N_PATH . 'includes/notifiers/class-f2n-slack.php';

add_action( 'plugins_loaded', function () {
    ( new F2N_Plugin() )->init();
    ( new F2N_Admin() )->init();
} );
