<?php
/**
 * Nagad Payment Gateway
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */

namespace ultraDevs\BDPG\Gateways;

use ultraDevs\BDPG\BDPG_Gateway;

/**
 * Nagad Payment Gateway class.
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */
class Nagad extends BDPG_Gateway {

    public function __construct() {
        $this->gateway = 'nagad';

        parent::__construct();
    }

}
