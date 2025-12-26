<?php
/**
 * Nagad Blocks Support Class
 *
 * @package BDPaymentGateways
 * @since 3.0.6
 */

namespace ultraDevs\BDPG\Blocks\Gateways;

use ultraDevs\BDPG\Blocks\BDPG_Gateway_Blocks_Support;
use ultraDevs\BDPG\Traits\Singleton;

/**
 * Nagad_Blocks Class
 *
 * @package BDPaymentGateways
 * @since 3.0.6
 */
class Nagad_Blocks extends BDPG_Gateway_Blocks_Support {
	use Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->gateway = 'nagad';
		$this->name    = 'woo_nagad';
	}
}
