<?php
/**
 * Upay Blocks Support Class
 *
 * @package BDPaymentGateways
 * @since 3.0.6
 */

namespace ultraDevs\BDPG\Blocks\Gateways;

use ultraDevs\BDPG\Blocks\BDPG_Gateway_Blocks_Support;
use ultraDevs\BDPG\Traits\Singleton;

/**
 * Upay_Blocks Class
 *
 * @package BDPaymentGateways
 * @since 3.0.6
 */
class Upay_Blocks extends BDPG_Gateway_Blocks_Support {
	use Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->gateway = 'upay';
		$this->name    = 'woo_upay';
	}
}
