<?php
/**
 * bKash Blocks Support Class
 *
 * @package BDPaymentGateways
 * @since 3.0.6
 */

namespace ultraDevs\BDPG\Blocks\Gateways;

use ultraDevs\BDPG\Blocks\BDPG_Gateway_Blocks_Support;
use ultraDevs\BDPG\Traits\Singleton;

/**
 * Bkash_Blocks Class
 *
 * @package BDPaymentGateways
 * @since 3.0.6
 */
class Bkash_Blocks extends BDPG_Gateway_Blocks_Support {
	use Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->gateway = 'bkash';
		$this->name    = 'woo_bkash';
	}
}
