<?php
/**
 * Main Plugin Class
 *
 * @package gtg-product-blocks
 * @since 1.0.0
 */

use \Automattic\WooCommerce\Blocks\BlockTypes\AbstractProductGrid;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'GPB_Block_Library' ) ) :

	class GPB_Block_Library {

		/**
		 * block types storage
		 *
		 * @since 1.0.0
		 */
		public $block_types = array();

		/**
		 * block type for override
		 */
		public $block_type_overrides = array();

		/**
		 * contructor class
		 *
		 * override block types
		 *
		 * register assets
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'init', array( __CLASS__, 'register_assets' ), 10 );
			add_action( 'init', array( $this, 'register_block_types' ), 10 );
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ), 1 );
			add_filter( 'register_block_type_args', array( __CLASS__, 'woocommerce_custom_attributes' ), 10, 2 );
			add_filter( 'register_block_type_args', array( $this, 'register_block_type_overrides' ), 10, 2 );
		}

		/**
		 * get block types list
		 *
		 * @since 1.0.0
		 */
		public function get_block_types() {
			return $this->block_types = array(
				'ProductLookBook'
			);
		}

		/**
		 * get block types to override
		 *
		 * @since 1.0.0
		 */
		public function get_block_type_overrides() {
			return $this->block_type_overrides = array(
				'woocommerce/featured-category' => 'FeaturedCategory'
			);
		}

		/**
		 * register all block assets handler
		 *
		 * @since 1.0.0
		 */
		public static function register_assets() {
			wp_register_script( 'gpb-editor', self::get_file_url( 'build/editor.js' ), array( 'jquery' ), GTG_PRODUCT_VERSION, false );
			// gutengeek
			wp_register_script( 'gpb-product-gutengeek', self::get_file_url( 'build/gutengeek.js' ), array( 'gutengeek-components', 'wc-blocks', 'wc-vendors' ), GTG_PRODUCT_VERSION, true );

			// tooltipter
			wp_register_script( 'popperjs', self::get_file_url( 'assets/libs/popperjs/popper.min.js' ), array( 'wc-blocks', 'wc-vendors' ), GTG_PRODUCT_VERSION, true );
			wp_register_script( 'tippyjs', self::get_file_url( 'assets/libs/tippyjs/dist/tippy-bundle.umd.min.js' ), array( 'wc-blocks', 'wc-vendors' ), GTG_PRODUCT_VERSION, true );
			wp_register_style( 'tippyjs', self::get_file_url( 'assets/libs/tippyjs/dist/tippy.css' ), array(), GTG_PRODUCT_VERSION, 'all' );
			// product lookbook
			wp_register_script( 'gpb-product-lookbook', self::get_file_url( 'build/product-lookbook.js' ), array( 'wc-blocks', 'wc-vendors' ), GTG_PRODUCT_VERSION, true );
			wp_register_style( 'gpb-product-lookbook', self::get_file_url( 'build/product-lookbook.css' ), array(), GTG_PRODUCT_VERSION, 'all' );
			// loobk frontend
			wp_register_script( 'gpb-product-lookbook-frontend', self::get_file_url( 'build/product-lookbook-frontend.js' ), array( 'wp-url', 'wp-api-fetch', 'popperjs', 'tippyjs' ), GTG_PRODUCT_VERSION, true );

			// featured category
			wp_register_script( 'gpb-featured-category', self::get_file_url( 'build/featured-category.js' ), array( 'wc-featured-category' ), GTG_PRODUCT_VERSION, true );
		}

		public static function enqueue_editor_assets() {
			wp_enqueue_script( 'gpb-editor' );
			wp_enqueue_script( 'gpb-featured-category' );
		}

		/**
		 * register all block types
		 *
		 * @since 1.0.0
		 */
		public function register_block_types() {
			$block_types = $this->get_block_types();
			foreach ( $block_types as $block_type ) {
				$classname = '\\GPB\\BlockTypes\\' . $block_type;
				$instance = new $classname();
				$instance->register_block_type();
				$this->block_types[$block_type] = $instance;
			}
		}

		/**
		 * get block build asset url
		 *
		 * @param string $file
		 * @return string file url
		 */
		public static function get_file_url( $file = '' ) {
			return trailingslashit( GTG_PRODUCT_URL ) . $file;
		}

		/**
		 * add custom attributes to woocommerce blocks
		 *
		 * @param array $args
		 * @param string $name
		 * @return array $args
		 * @since 1.0.6
		 */
		public static function woocommerce_custom_attributes( $args, $name ) {
			if ( strpos( $name, 'gpb/' ) !== false || strpos( $name, 'woocommerce/' ) !== false ) {
				$args['attributes'] = isset( $args['attributes'] ) ? $args['attributes'] : array();
				$args['attributes'] = array_merge( $args['attributes'], [
					'gutengeekCustomCSS' => [
						'type' => 'string',
						'default' => ''
					],
					'gutengeekAnimation' => [
						'type' => 'object',
						'default' => [
							'type' => '',
							'speed' => '',
							'delay' => ''
						]
					],
					'blockAnimation' => [
						'type' => 'object',
						'default' => [
							'name' => '',
							'direction' => '',
							'repeat' => false,
							'delay' => '',
							'duration' => 400,
							'timing' => 'ease-in'
						],
					],
					'gutengeekResponsive' => [
						'type' => 'object',
						'default' => [
							'hideDesktop' => false,
							'hideTablet' => false,
							'hideMobile' => false
						]
					]
				] );
			}

			return $args;
		}

		/**
		 * override WooCommerce block type
		 *
		 * @param array $args
		 * @param string $name
		 * @return array $args
		 */
		public function register_block_type_overrides( $args, $name ) {
			$block_types = $this->get_block_type_overrides();
			if ( isset( $block_types[$name] ) ) {
				$block_type = $block_types[$name];
				$classname = '\\GPB\\BlockTypes\\' . $block_type;
				$instance = new $classname();

				$attributes = $instance->get_attributes_override();
				$args['attributes'] = array_merge( $args['attributes'], $attributes );
				$args['render_callback'] = array( $instance, 'render' );
			}

			return $args;
		}

	}

endif;
