<?php
// アンインストール時にオプション掃除
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

delete_option( 'f2n_settings' );
delete_option( 'f2n_logs' );