<?php
/**
 * Plugin Name: Shipping Extras
 * Version: 0.1.0
 * Author: Clara Daia
 * Author URI: https://github.com/claradaia
 * Text Domain: shipping-extras
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package extension
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'MAIN_PLUGIN_FILE' ) ) {
	define( 'MAIN_PLUGIN_FILE', __FILE__ );
}

/**
 * WooCommerce fallback notice.
 *
 * @since 0.1.0
 */
function shipping_extras_missing_wc_notice() {
	/* translators: %s WC download URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Shipping Extras requires WooCommerce to be installed and active. You can download %s here.', 'shipping_extras' ), '<a href="https://woo.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

register_activation_hook( __FILE__, 'shipping_extras_activate' );

/**
 * Activation hook.
 *
 * @since 0.1.0
 */
function shipping_extras_activate() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'shipping_extras_missing_wc_notice' );
		return;
	}

	if ( ! get_role( 'VIP_customer' ) ) {
		// add VIP role, if it doesn't exist
		$customer = get_role( 'customer' );
		$VIP_capabilities = $customer->capabilities;

		// VIP capabilities expand regular customer capabilities with "free shipping"
		array_push($VIP_capabilities, 'shipping_extras_free_shipping');

        add_role(
            'VIP_customer',
            'VIP Customer',
            $VIP_capabilities
        );

		// register that role was created by shipping-extras, so it can be removed upon deactivation
		update_option( 'shipping_extras_VIP_role_created', true );

		error_log('[shipping-extras] Created VIP customer role.');
    } else {
		// expand existing VIP customer capabilities with "free shipping"
		$VIP_customer = get_role( 'VIP_customer' );
		$VIP_customer->add_cap('shipping_extras_free_shipping');

		error_log('[shipping-extras] Added \'free shipping\' to existing VIP customer role.');

	}

	error_log('[shipping-extras] Plugin activated.');
}


register_deactivation_hook( __FILE__, 'shipping_extras_deactivate' );

/**
 * Deactivation hook.
 *
 * @since 0.1.0
 */
function shipping_extras_deactivate() {
	// VIP customers become regular customers
	$users = get_users( array( 'role' => 'VIP_customer' ) );
	foreach ( $users as $user ) {
		$user->set_role( 'customer' );
	}

	if ( get_option( 'shipping_extras_VIP_role_created' )) {
		// remove VIP role, if created only for the plugin
		remove_role('VIP_customer');
		delete_option('shipping_extras_VIP_role_created');
		error_log('[shipping-extras] Removed VIP customer role.');
	} else {
		// remove "free shipping" capability from existing VIP customer role
		$VIP_customer = get_role( 'VIP_customer' );
		$VIP_customer->remove_cap('shipping_extras_free_shipping');
		error_log('[shipping-extras] Removed \'free-shipping\' from VIP customer role.');
	}

	error_log('[shipping-extras] Plugin deactivated.');

}



if ( ! class_exists( 'shipping_extras' ) ) :
	/**
	 * The shipping_extras class.
	 */
	class shipping_extras {
		/**
		 * This class instance.
		 *
		 * @var \shipping_extras single instance of this class.
		 */
		private static $instance;

		/**
		 * Constructor.
		 */
		public function __construct() {

			add_filter( 'woocommerce_package_rates', [$this, 'shipping_rates_from_cart_subtotal'], 10, 2 );

			 // user roles have priority over subtotal logic, so that if rates are zero no discount message is shown
			add_filter( 'woocommerce_package_rates', [$this, 'shipping_rates_from_user_role'], 9, 2 );

		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'shipping_extras' ), $this->version );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'shipping_extras' ), $this->version );
		}

		/**
		 * Gets the main instance.
		 *
		 * Ensures only one instance can be loaded.
		 *
		 * @return \shipping_extras
		 */
		public static function instance() {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		const SHIPPING_DISCOUNT_TIERS = array (
			100 => 0.1,
			150 => 0.05,
			200 => 0.025
		);

		public function shipping_rates_from_cart_subtotal( $rates, $package ) {
			$subtotal = WC()->cart->subtotal;
			$discount_rate = 0;

			// find discount tier
			foreach (self::SHIPPING_DISCOUNT_TIERS as $threshold => $rate) {
				if ($subtotal <= $threshold) {
					break;
				}
				$discount_rate = $rate;
			}

			// apply discount to all rates
			if ( $discount_rate > 0) {
				array_walk($rates, function(&$rate) use ($discount_rate) {
					if ( $rate->cost > 0 ) {
						$rate->cost = $rate->cost * (1 - $discount_rate);
						// tell user they're getting a discount
						$rate->label = $rate->label . " (" . ( $discount_rate * 100 ) . "% discount! )";
					}
				});
			}

			return $rates;
		}

		function shipping_rates_from_user_role($rates, $package) {
			$user = wp_get_current_user();

			if ( in_array( 'VIP_customer', $user->roles ) ) {
				array_walk($rates, function(&$rate) {
					$rate->cost = 0;
					// tell user they're getting it for free
					$rate->label = $rate->label . " (VIP)";
				});
			}

			return $rates;
		}
	}
endif;

add_action( 'plugins_loaded', 'shipping_extras_init', 10 );

/**
 * Initialize the plugin.
 *
 * @since 0.1.0
 */
function shipping_extras_init() {

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'shipping_extras_missing_wc_notice' );
		return;
	}

	shipping_extras::instance();

}
