<?php
/**
 * Singleton Trait
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */
namespace ultraDevs\BDPG\Traits;

/**
 * Singleton Trait
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */
trait Singleton {

	/**
	 * Instance - Singleton Pattern
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Get Instance
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Wakeup
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize a singleton.' );
	}
}
