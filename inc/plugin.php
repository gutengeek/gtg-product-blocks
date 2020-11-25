<?php
/**
 * Main Plugin Class
 *
 * @package gtg-product-blocks
 * @since 1.0.0
 */

namespace GPB;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'GTG_Product_Blocks' ) ) :

	final class GTG_Product_Blocks {

		public static $instance;

		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			$this->includes();
			$this->init_hooks();
		}

		public function init_hooks() {
			add_action( 'plugins_loaded', array( $this, 'register_text_domain' ) );
		}

		/**
		 * include needed files
		 *
		 * @since 1.0.0
		 */
		public function includes() {
			include_once GTG_PRODUCT_ROOT . '/inc/class-block-library.php';
			$this->block_library = new \GPB_Block_Library();
		}

		/**
		 * register plugin text domain
		 */
		public function register_text_domain() {

		}

	}

endif;

if ( ! function_exists( 'gtg_product_blocks' ) ) {

	/**
	 * init instance class
	 */
	function gtg_product_blocks() {
		return GTG_Product_Blocks::instance();
	}

}

gtg_product_blocks();
