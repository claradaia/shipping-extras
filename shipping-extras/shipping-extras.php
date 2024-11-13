<?php
/**
 * Plugin Name: Shipping Extras
 * Version: 0.1.0
 * Author: Clara Daia
 * Author URI: https://github.com/claradaia
 * Text Domain: shipping-extras
 * Domain Path: /languages
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

require_once __DIR__ . '/includes/admin/setup.php';

use ShippingExtras\Admin\Setup;

// phpcs:disable WordPress.Files.FileName

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
			if ( is_admin() ) {
				new Setup();
			}
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
	}
endif;

add_action( 'plugins_loaded', 'shipping_extras_init', 10 );

/**
 * Initialize the plugin.
 *
 * @since 0.1.0
 */
function shipping_extras_init() {
	load_plugin_textdomain( 'shipping_extras', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'shipping_extras_missing_wc_notice' );
		return;
	}

	shipping_extras::instance();

}
