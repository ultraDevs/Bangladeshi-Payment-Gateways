<?php
/**
 * Bangla QR Blocks Support Class
 *
 * @package BDPaymentGateways
 * @since 4.0.5
 */

namespace ultraDevs\BDPG\Blocks\Gateways;

use ultraDevs\BDPG\Blocks\BDPG_Gateway_Blocks_Support;
use ultraDevs\BDPG\Traits\Singleton;

/**
 * Bangla_QR_Blocks Class
 *
 * @package BDPaymentGateways
 * @since 4.0.5
 */
class Bangla_QR_Blocks extends BDPG_Gateway_Blocks_Support {
	use Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->gateway = 'bangla_qr';
		$this->name    = 'woo_bangla_qr';
	}
}
