<?php
/**
 * Plugin Name: GTG Product Blocks
 * Plugin URI: https://profiles.wordpress.org/gutengeek/
 * Description: Product gutenberg blocks, designs everything you need for gutenberg editor
 * Author: GutenGeek
 * Author URI: https://gutengeek.com/
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package gtg-product-blocks
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit();

define( 'GTG_PRODUCT_VERSION', '1.0.0' );
define( 'GTG_PRODUCT_FILE', __FILE__ );
define( 'GTG_PRODUCT_ROOT', dirname( GTG_PRODUCT_FILE ) );
define( 'GTG_PRODUCT_URL', plugin_dir_url( __FILE__ ) );

// include autoload file
require_once GTG_PRODUCT_ROOT . '/vendor/autoload.php';

add_action( 'plugins_loaded', 'gpb_woo_loaded' );

if ( ! function_exists( 'gpb_woo_loaded' ) ) {
	/**
	 * Determine WooCommerce Plugin installed and activated
	 *
	 * @since 1.0.0
	 */
	function gpb_woo_loaded() {
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'gtg_advanced_blocks' ) ) {
			add_action( 'admin_notices', 'gpb_woo_notice' );
		} else {
			include_once GTG_PRODUCT_ROOT . '/inc/plugin.php';
		}
	}
}

if ( ! function_exists( 'gpb_woo_notice' ) ) {
	/**
	 * admin notice
	 *
	 * User need to install and activate WooCommerce plugin
	 */
	function gpb_woo_notice() {
		?>
			<?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
				<div class="notice notice-error">
			        <p><?php printf( __( 'GTG Product Blocks is enabled but not effective. It requires <a href="%s" target="_blank">WooCommerce</a> in order to work.', 'gtg-product-blocks' ), 'https://wordpress.org/plugins/woocommerce' ); ?></p>
			    </div>
			<?php endif; ?>
			<?php if ( ! function_exists( 'gtg_advanced_blocks' ) ) : ?>
				<div class="notice notice-error">
			        <p><?php printf( __( 'GTG Product Blocks is enabled but not effective. It requires <a href="%s" target="_blank">GutenGeek</a> in order to work.', 'gtg-product-blocks' ), 'https://wordpress.org/plugins/gtg-advanced-blocks' ); ?></p>
			    </div>
			<?php endif; ?>
		<?php
	}
}
