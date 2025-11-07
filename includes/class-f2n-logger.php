<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class F2N_Logger {

    const LIMIT = 50; // 直近50件まで保持（将来はCPT/DB移行）

    public static function push( array $payload ) {
        $logs = get_option( F2N_LOG_OPTION_KEY, [] );
        $logs[] = $payload;
        if ( count($logs) > self::LIMIT ) {
            $logs = array_slice( $logs, - self::LIMIT );
        }
        update_option( F2N_LOG_OPTION_KEY, $logs, false );
    }

    public static function get_all() {
        $logs = get_option( F2N_LOG_OPTION_KEY, [] );
        if ( ! is_array($logs) ) $logs = [];
        return $logs;
    }
}
