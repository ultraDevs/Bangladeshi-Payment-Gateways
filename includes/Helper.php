<?php // @codingStandardsIgnoreLine
/**
 * Helper Class
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */

namespace ultraDevs\BDPG;

/**
 * Helper Class
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */
class Helper {


	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Add Option
	 *
	 * @param string $key Option Key.
	 * @param mixed  $value Option Value.
	 */
	public static function add_option( $key, $value ) {
		// Get Option.
		add_option( $key, $value );
	}

	/**
	 * Get Option
	 *
	 * @param string $key Option Key.
	 * @param mixed  $default Option Default.
	 */
	public static function get_option( $key, $default = false ) {
		// Get Option.
		$value = get_option( $key, $default );
		return $value;
	}

	/**
	 * Save Option
	 *
	 * @param string $key Option Key.
	 * @param mixed  $value Option Value.
	 */
	public static function update_option( $key, $value ) {
		// Get Option.
		update_option( $key, $value );
	}

	/**
	 * Time to Day(s) Converter
	 *
	 * @param int $time Time.
	 * @return int
	 */
	public static function time_to_days( $time ) {

		$current_time = current_time( 'timestamp' ); //phpcs:ignore
		return round( ( $current_time - $time ) / 24 / 60 / 60 );
	}


	/**
	 * Get All Meta values by meta key.
	 *
	 * @param string $meta_key Meta Key.
	 * @param string $post_type Post Type.
	 * @return array
	 */
	public static function get_meta_values( $meta_key, $post_type = 'post' ) {

		$posts = get_posts(
			array(
				'post_type'      => $post_type,
				'meta_key'       => $meta_key,
				'posts_per_page' => -1,
			)
		);

		$meta_values = array();
		foreach( $posts as $post ) {
			$meta_values[] = get_post_meta( $post->ID, $meta_key, true );
		}

		return $meta_values;

	}
}
