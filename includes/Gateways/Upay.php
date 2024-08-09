<?php
/**
 * Upay Payment Gateway
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */

namespace ultraDevs\BDPG\Gateways;

use ultraDevs\BDPG\BDPG_Gateway;

/**
 * Upay Payment Gateway class.
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */
class Upay extends BDPG_Gateway {

    public function __construct() {
        $this->gateway = 'upay';

        parent::__construct();
    }

}
