<?php
/**
 * Rocket Payment Gateway
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */

namespace ultraDevs\BDPG\Gateways;

use ultraDevs\BDPG\BDPG_Gateway;
use ultraDevs\BDPG\Traits\Singleton;

/**
 * Rocket Payment Gateway class.
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */
class Rocket extends BDPG_Gateway {
    use Singleton;

    public function __construct() {
        $this->gateway = 'rocket';

        parent::__construct();
    }

}
