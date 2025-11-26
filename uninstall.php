<?php
/**
 * Cleanup uninstall routine for Fail2Notify.
 *
 * @package Fail2Notify\MailFailureAlerts
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'fail2notify_settings' );
delete_option( 'fail2notify_logs' );
