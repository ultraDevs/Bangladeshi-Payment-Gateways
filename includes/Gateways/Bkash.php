<?php
/**
 * bKash Payment Gateway
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */

namespace ultraDevs\BDPG\Gateways;

use ultraDevs\BDPG\BDPG_Gateway;
use ultraDevs\BDPG\Traits\Singleton;

/**
 * bKash Payment Gateway class.
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */
class Bkash extends BDPG_Gateway {
    use Singleton;

    public function __construct() {
        $this->gateway = 'bkash';

        parent::__construct();
    }

}
