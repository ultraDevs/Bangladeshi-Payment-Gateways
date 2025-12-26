<?php
/**
 * Admin Dashboard
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */

namespace ultraDevs\BDPG\Admin;

use ultraDevs\BDPG\Helper;

/**
 * Dashboard Class
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */
class Dashboard {

	/**
	 * Option key for currency settings
	 *
	 * @var string
	 */
	public const CURRENCY_SETTINGS_OPTION = 'bdpg_currency_settings';

	/**
	 * Default USD rate
	 *
	 * @var float
	 */
	public const DEFAULT_USD_RATE = 123.00;

	/**
	 * Menu slug
	 *
	 * @var string
	 */
	public const MENU_SLUG = 'bangladeshi-payment-gateways';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_notices', [ $this, 'hide_dashboard_admin_notices' ], 0 );
	}

	/**
	 * Hide admin notices on dashboard page only
	 *
	 * @return void
	 */
	public function hide_dashboard_admin_notices() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		if ( 'toplevel_page_' . self::MENU_SLUG === $screen->id ) {
			remove_all_actions( 'admin_notices' );
		}
	}

	/**
	 * Register admin menu
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'Bangladeshi Payment Gateways', 'bangladeshi-payment-gateways' ),
			__( 'BD Payment', 'bangladeshi-payment-gateways' ),
			'manage_woocommerce',
			self::MENU_SLUG,
			[ $this, 'render_dashboard_page' ],
			'dashicons-money-alt',
			30
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Dashboard', 'bangladeshi-payment-gateways' ),
			__( 'Dashboard', 'bangladeshi-payment-gateways' ),
			'manage_woocommerce',
			self::MENU_SLUG,
			[ $this, 'render_dashboard_page' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'bangladeshi-payment-gateways' ),
			__( 'Settings', 'bangladeshi-payment-gateways' ),
			'manage_woocommerce',
			self::MENU_SLUG . '-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register settings
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'bdpg_settings',
			self::CURRENCY_SETTINGS_OPTION,
			[
				'type'              => 'array',
				'default'           => [
					'enable_usd_conversion'   => 'yes',
					'show_conversion_details' => 'yes',
					'usd_rate'                => self::DEFAULT_USD_RATE,
				],
				'sanitize_callback' => [ $this, 'sanitize_currency_settings' ],
			]
		);
	}

	/**
	 * Sanitize currency settings
	 *
	 * @param array $value Input values.
	 * @return array
	 */
	public function sanitize_currency_settings( $value ) {
		$sanitized = array();

		// Sanitize enable_usd_conversion.
		$sanitized['enable_usd_conversion'] = isset( $value['enable_usd_conversion'] ) && 'yes' === $value['enable_usd_conversion'] ? 'yes' : 'no';

		// Sanitize show_conversion_details.
		$sanitized['show_conversion_details'] = isset( $value['show_conversion_details'] ) && 'yes' === $value['show_conversion_details'] ? 'yes' : 'no';

		// Sanitize usd_rate.
		$rate                  = isset( $value['usd_rate'] ) ? floatval( $value['usd_rate'] ) : self::DEFAULT_USD_RATE;
		$sanitized['usd_rate'] = $rate > 0 ? $rate : self::DEFAULT_USD_RATE;

		return $sanitized;
	}

	/**
	 * Render dashboard page
	 *
	 * @return void
	 */
	public function render_dashboard_page() {
		?>
		<div class="wrap">
			<div class="bdpg-admin-wrap">
				<div class="bdpg-content">
					<div class="bdpg-card">
						<div class="bdpg-card-header">
							<div class="bdpg-title-section">
								<h1>
									<span class="dashicons dashicons-money-alt"></span>
									<?php esc_html_e( 'Bangladeshi Payment Gateways', 'bangladeshi-payment-gateways' ); ?>
								</h1>
								<span class="bdpg-version"><?php echo esc_html( 'v' . BD_PAYMENT_GATEWAYS_VERSION ); ?></span>
							</div>
						</div>

						<div class="bdpg-card-body">
							<p><?php esc_html_e( 'Thank you for using Bangladeshi Payment Gateways. This plugin allows you to accept payments through bKash, Rocket, Nagad, and Upay using QR codes in your WooCommerce store.', 'bangladeshi-payment-gateways' ); ?></p>

							<div class="bdpg-quick-links">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ); ?>" class="button button-primary">
									<span class="dashicons dashicons-admin-generic"></span>
									<?php esc_html_e( 'Configure Payment Gateways', 'bangladeshi-payment-gateways' ); ?>
								</a>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG . '-settings' ) ); ?>" class="button">
									<span class="dashicons dashicons-admin-settings"></span>
									<?php esc_html_e( 'Plugin Settings', 'bangladeshi-payment-gateways' ); ?>
								</a>
							</div>
						</div>
					</div>

					<?php $this->render_features_section(); ?>
					<?php $this->render_plugins_section(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render features section
	 *
	 * @return void
	 */
	private function render_features_section() {
		?>
		<div class="bdpg-card bdpg-features-card">
			<div class="bdpg-card-header">
				<h2>
					<span class="dashicons dashicons-superhero"></span>
					<?php esc_html_e( 'Features', 'bangladeshi-payment-gateways' ); ?>
				</h2>
			</div>
			<div class="bdpg-card-body">
				<div class="bdpg-features-grid">
					<div class="bdpg-feature-item">
						<div class="bdpg-feature-icon">
							<span class="dashicons dashicons-screenoptions"></span>
						</div>
						<div class="bdpg-feature-content">
							<h3><?php esc_html_e( 'WooCommerce Checkout Block Support', 'bangladeshi-payment-gateways' ); ?></h3>
							<p><?php esc_html_e( 'Full compatibility with the modern WooCommerce Checkout Block. Your customers get a seamless, modern checkout experience.', 'bangladeshi-payment-gateways' ); ?></p>
						</div>
					</div>
					<div class="bdpg-feature-item">
						<div class="bdpg-feature-icon">
							<span class="dashicons dashicons-format-image"></span>
						</div>
						<div class="bdpg-feature-content">
							<h3><?php esc_html_e( 'QR Code Payments', 'bangladeshi-payment-gateways' ); ?></h3>
							<p><?php esc_html_e( 'Display QR codes for all supported gateways (bKash, Rocket, Nagad, Upay) making payments quick and easy.', 'bangladeshi-payment-gateways' ); ?></p>
						</div>
					</div>
					<div class="bdpg-feature-item">
						<div class="bdpg-feature-icon">
							<span class="dashicons dashicons-money-alt"></span>
						</div>
						<div class="bdpg-feature-content">
							<h3><?php esc_html_e( 'Automatic Currency Conversion', 'bangladeshi-payment-gateways' ); ?></h3>
							<p><?php esc_html_e( 'Convert USD to BDT automatically at your set exchange rate when store currency is USD.', 'bangladeshi-payment-gateways' ); ?></p>
						</div>
					</div>
					<div class="bdpg-feature-item">
						<div class="bdpg-feature-icon">
							<span class="dashicons dashicons-chart-line"></span>
						</div>
						<div class="bdpg-feature-content">
							<h3><?php esc_html_e( 'Gateway Fee Support', 'bangladeshi-payment-gateways' ); ?></h3>
							<p><?php esc_html_e( 'Add custom gateway fees and automatically calculate total payment amount including fees.', 'bangladeshi-payment-gateways' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render plugins section
	 *
	 * @return void
	 */
	private function render_plugins_section() {
		$plugins = $this->get_ultradevs_plugins();
		?>
		<div class="bdpg-card">
			<div class="bdpg-card-body">
				<h2><?php esc_html_e( 'More Plugins by ultraDevs', 'bangladeshi-payment-gateways' ); ?></h2>
				<p><?php esc_html_e( 'Check out our other WordPress plugins to enhance your website:', 'bangladeshi-payment-gateways' ); ?></p>

				<div class="bdpg-plugins-grid">
					<?php
					foreach ( $plugins as $plugin ) {
						$this->render_plugin_card( $plugin );
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single plugin card
	 *
	 * @param array $plugin Plugin data.
	 * @return void
	 */
	private function render_plugin_card( $plugin ) {
		$slug       = $plugin['slug'] ?? '';
		$name       = $plugin['name'] ?? '';
		$desc       = $plugin['description'] ?? '';
		$wporg_link = 'https://wordpress.org/plugins/' . $slug . '/';
		$installed  = $this->is_plugin_installed( $slug );
		$is_active  = $this->is_plugin_active( $slug );
		?>
		<div class="bdpg-plugin-card">
			<div class="bdpg-plugin-header">
				<h3>
					<?php echo esc_html( $name ); ?>
					<?php if ( $is_active ) : ?>
						<span class="bdpg-badge bdpg-badge-active"><?php esc_html_e( 'Active', 'bangladeshi-payment-gateways' ); ?></span>
					<?php elseif ( $installed ) : ?>
						<span class="bdpg-badge bdpg-badge-installed"><?php esc_html_e( 'Installed', 'bangladeshi-payment-gateways' ); ?></span>
					<?php endif; ?>
				</h3>
			</div>
			<div class="bdpg-plugin-body">
				<p><?php echo esc_html( $desc ); ?></p>
				<div class="bdpg-plugin-actions">
					<?php if ( ! $installed ) : ?>
						<?php
						$install_url = wp_nonce_url(
							add_query_arg(
								[
									'action' => 'install-plugin',
									'plugin' => $slug,
								],
								self_admin_url( 'update.php' )
							),
							'install-plugin_' . $slug
						);
						?>
						<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary">
							<?php esc_html_e( 'Install Now', 'bangladeshi-payment-gateways' ); ?>
						</a>
					<?php elseif ( ! $is_active ) : ?>
						<?php
						$activate_url = wp_nonce_url(
							add_query_arg(
								[
									'action' => 'activate',
									'plugin' => $slug . '/' . $slug . '.php',
								],
								admin_url( 'plugins.php' )
							),
							'activate-plugin_' . $slug . '/' . $slug . '.php'
						);
						?>
						<a href="<?php echo esc_url( $activate_url ); ?>" class="button button-primary">
							<?php esc_html_e( 'Activate', 'bangladeshi-payment-gateways' ); ?>
						</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( $wporg_link ); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary">
						<?php esc_html_e( 'View Details', 'bangladeshi-payment-gateways' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Check if plugin is installed
	 *
	 * @param string $slug Plugin slug.
	 * @return bool
	 */
	private function is_plugin_installed( $slug ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( strpos( $plugin_file, $slug . '/' ) === 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if plugin is active
	 *
	 * @param string $slug Plugin slug.
	 * @return bool
	 */
	private function is_plugin_active( $slug ) {
		return is_plugin_active( $slug . '/' . $slug . '.php' );
	}

	/**
	 * Get ultraDevs plugins list
	 *
	 * @return array
	 */
	private function get_ultradevs_plugins() {
		return [
			[
				'slug'        => 'easy-dropbox-integration',
				'name'        => __( 'Easy Dropbox Integration', 'bangladeshi-payment-gateways' ),
				'description' => __( 'Integrate Dropbox with your WordPress site for easy file management and backups.', 'bangladeshi-payment-gateways' ),
			],
			[
				'slug'        => 'sticky-list',
				'name'        => __( 'Sticky List', 'bangladeshi-payment-gateways' ),
				'description' => __( 'Keep important posts and pages at the top of your lists with sticky functionality.', 'bangladeshi-payment-gateways' ),
			],
			[
				'slug'        => 'pb-star-rating-block',
				'name'        => __( 'PB Star Rating Block', 'bangladeshi-payment-gateways' ),
				'description' => __( 'Add beautiful star rating blocks to your posts and pages with the block editor.', 'bangladeshi-payment-gateways' ),
			],
			[
				'slug'        => 'testimonialx-block',
				'name'        => __( 'TestimonialX Block', 'bangladeshi-payment-gateways' ),
				'description' => __( 'Display customer testimonials with style using the Gutenberg block editor.', 'bangladeshi-payment-gateways' ),
			],
			[
				'slug'        => 'random-image-block-for-block-editor',
				'name'        => __( 'Random Image Block', 'bangladeshi-payment-gateways' ),
				'description' => __( 'Display random images from your media library with this handy block editor plugin.', 'bangladeshi-payment-gateways' ),
			],
		];
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		$currency_settings = Helper::get_option( self::CURRENCY_SETTINGS_OPTION, [] );
		$settings          = wp_parse_args(
			$currency_settings,
			[
				'enable_usd_conversion'   => 'yes',
				'show_conversion_details' => 'yes',
				'usd_rate'                => self::DEFAULT_USD_RATE,
			]
		);

		$enable_usd_conversion   = $settings['enable_usd_conversion'];
		$show_conversion_details = $settings['show_conversion_details'];
		$usd_rate                = $settings['usd_rate'];
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Bangladeshi Payment Gateways Settings', 'bangladeshi-payment-gateways' ); ?></h1>

			<div class="bdpg-settings-wrapper">
				<form method="post" action="options.php">
					<?php
					settings_fields( 'bdpg_settings' );
					do_settings_sections( 'bdpg_settings' );
					?>

					<div class="bdpg-settings-card">
						<h2><?php esc_html_e( 'Currency Settings', 'bangladeshi-payment-gateways' ); ?></h2>

						<table class="form-table">
							<tr>
								<th scope="row">
									<label>
										<?php esc_html_e( 'Enable USD Conversion', 'bangladeshi-payment-gateways' ); ?>
									</label>
								</th>
								<td>
									<fieldset>
										<label>
											<input
												type="radio"
												name="<?php echo esc_attr( self::CURRENCY_SETTINGS_OPTION ); ?>[enable_usd_conversion]"
												value="yes"
												<?php checked( $enable_usd_conversion, 'yes' ); ?>
											>
											<?php esc_html_e( 'Yes', 'bangladeshi-payment-gateways' ); ?>
										</label>
										<br>
										<label>
											<input
												type="radio"
												name="<?php echo esc_attr( self::CURRENCY_SETTINGS_OPTION ); ?>[enable_usd_conversion]"
												value="no"
												<?php checked( $enable_usd_conversion, 'no' ); ?>
											>
											<?php esc_html_e( 'No', 'bangladeshi-payment-gateways' ); ?>
										</label>
									</fieldset>
									<p class="description">
										<?php esc_html_e( 'When enabled, if your store currency is set to USD, the payment amount will be automatically converted to BDT using the exchange rate below.', 'bangladeshi-payment-gateways' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label>
										<?php esc_html_e( 'Show Conversion Details', 'bangladeshi-payment-gateways' ); ?>
									</label>
								</th>
								<td>
									<fieldset>
										<label>
											<input
												type="radio"
												name="<?php echo esc_attr( self::CURRENCY_SETTINGS_OPTION ); ?>[show_conversion_details]"
												value="yes"
												<?php checked( $show_conversion_details, 'yes' ); ?>
											>
											<?php esc_html_e( 'Yes', 'bangladeshi-payment-gateways' ); ?>
										</label>
										<br>
										<label>
											<input
												type="radio"
												name="<?php echo esc_attr( self::CURRENCY_SETTINGS_OPTION ); ?>[show_conversion_details]"
												value="no"
												<?php checked( $show_conversion_details, 'no' ); ?>
											>
											<?php esc_html_e( 'No', 'bangladeshi-payment-gateways' ); ?>
										</label>
									</fieldset>
									<p class="description">
										<?php esc_html_e( 'Show the conversion details (original amount, exchange rate) to customers on the checkout page.', 'bangladeshi-payment-gateways' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label>
										<?php esc_html_e( 'USD to BDT Exchange Rate', 'bangladeshi-payment-gateways' ); ?>
									</label>
								</th>
								<td>
									<input
										type="number"
										name="<?php echo esc_attr( self::CURRENCY_SETTINGS_OPTION ); ?>[usd_rate]"
										value="<?php echo esc_attr( $usd_rate ); ?>"
										step="0.01"
										min="0"
										class="regular-text"
									>
									<p class="description">
										<?php esc_html_e( 'Enter the current exchange rate from USD to Bangladeshi Taka (BDT). For example, if 1 USD = 123 BDT, enter 123.', 'bangladeshi-payment-gateways' ); ?>
									</p>
								</td>
							</tr>
						</table>

						<?php submit_button( __( 'Save Settings', 'bangladeshi-payment-gateways' ) ); ?>
					</div>

					<div class="bdpg-settings-card">
						<h2><?php esc_html_e( 'Payment Gateway Settings', 'bangladeshi-payment-gateways' ); ?></h2>
						<p>
							<?php
							\printf(
								/* translators: %s: WooCommerce settings link */
								esc_html__( 'To configure individual payment gateway settings (bKash, Rocket, Nagad, Upay), please visit the %s.', 'bangladeshi-payment-gateways' ),
								\sprintf(
									'<a href="%s">%s</a>',
									esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ),
									esc_html__( 'WooCommerce Payment Settings', 'bangladeshi-payment-gateways' )
								)
							);
							?>
						</p>
						<p>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ); ?>" class="button button-primary">
								<?php esc_html_e( 'Go to Payment Gateway Settings', 'bangladeshi-payment-gateways' ); ?>
							</a>
						</p>
					</div>

					<div class="bdpg-settings-card bdpg-donation-card">
						<h2>
							<span class="dashicons dashicons-heart"></span>
							<?php esc_html_e( 'Support Development', 'bangladeshi-payment-gateways' ); ?>
						</h2>
						<p><?php esc_html_e( 'Enjoying this plugin? Your support helps us continue development and keep this plugin free for everyone.', 'bangladeshi-payment-gateways' ); ?></p>
						<div class="bdpg-donation-actions">
							<a href="https://ultradevs.com/donate/" target="_blank" rel="noopener noreferrer" class="button button-primary bdpg-donate-button" style="display: inline-flex">
								<span class="dashicons dashicons-star-filled"></span>
								<?php esc_html_e( 'Donate Us', 'bangladeshi-payment-gateways' ); ?>
							</a>
						</div>
						<div class="bdpg-support-links">
							<a href="https://wordpress.org/support/plugin/bangladeshi-payment-gateways/" target="_blank" rel="noopener noreferrer">
								<span class="dashicons dashicons-sos"></span>
								<?php esc_html_e( 'Support Forums', 'bangladeshi-payment-gateways' ); ?>
							</a>
							<a href="https://ultradevs.com/docs/" target="_blank" rel="noopener noreferrer">
								<span class="dashicons dashicons-book"></span>
								<?php esc_html_e( 'Plugin Documentation', 'bangladeshi-payment-gateways' ); ?>
							</a>
							<a href="https://wordpress.org/support/plugin/bangladeshi-payment-gateways/reviews?filter=5#new-post" target="_blank" rel="noopener noreferrer">
								<span class="dashicons dashicons-admin-comments"></span>
								<?php esc_html_e( 'Leave a Review', 'bangladeshi-payment-gateways' ); ?>
							</a>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php
	}
}
