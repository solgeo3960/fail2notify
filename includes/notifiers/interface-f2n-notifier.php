<?php
if ( ! defined( 'ABSPATH' ) ) exit;

interface F2N_Notifier_Interface {
    /**
     * @param array $payload 標準化ペイロード
     * @return bool 成功/失敗
     */
    public function notify( array $payload ): bool;
}
