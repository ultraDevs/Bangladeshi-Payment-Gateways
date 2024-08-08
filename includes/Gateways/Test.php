<?php
/**
 * Admin Dashboard
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */

namespace ultraDevs\BDPG\Gateways;

use ultraDevs\BDPG\BDPG_Gateway;

/**
 * Dashboard Class
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */
class Test extends BDPG_Gateway {

    public function __construct() {
        $this->gateway = 'test';

        parent::__construct();
    }

}
