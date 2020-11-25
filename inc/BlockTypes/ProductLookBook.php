<?php
/**
 * Product LooBook class
 *
 * @package gtg-product-blocks
 * @since 1.0.0
 */

namespace GPB\BlockTypes;

use Automattic\WooCommerce\Blocks\BlockTypes\AbstractProductGrid as AbstractProductGrid;

defined( 'ABSPATH' ) || exit();

class ProductLookBook extends AbstractProductGrid {

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'gpb';

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'product-lookbook';

	/**
	 * Default attribute values, should match what's set in JS `registerBlockType`.
	 *
	 * @var array
	 */
	protected $defaults = array(
		'align'        => 'none',
		'dimRatio'     => 50,
		'focalPoint'   => false,
		'height'       => false,
		'mediaId'      => 0,
		'mediaSrc'     => '',
	);

	public function __construct() {
		add_filter( 'rest_request_after_callbacks', array( $this, 'rest_request_after_callbacks' ), 10, 3 );
	}

	public function rest_request_after_callbacks($response, $handler, $request) {
		if ( $handler['callback'][0] instanceof \Automattic\WooCommerce\Blocks\StoreApi\Routes\ProductsById && ! is_wp_error( $response ) ) {
			$data = $response->get_data();
			$data['raw_html'] = $this->render_product( $data['id'] );
			$response->set_data( $data );
		}
		return $response;
	}

	/**
	 * Set args specific to this block
	 *
	 * @param array $query_args Query args.
	 */
	protected function set_block_query_args( &$query_args ) {
		$points = isset( $this->attributes['points'] ) ? $this->attributes['points'] : array();
		$ids = array_map( function( $point ) {
			return $point['productId'];
		}, $points );
		$query_args['post__in']       = $ids;
		$query_args['posts_per_page'] = count( $ids );
	}

	/**
	 * Registers the block type with WordPress.
	 */
	public function register_block_type() {
		register_block_type(
			$this->namespace . '/' . $this->block_name,
			array(
				'render_callback' => array( $this, 'render' ),
				'editor_script'   => 'gpb-' . $this->block_name,
				'style'           => array( 'gpb-' . $this->block_name, 'tippyjs' ),
				'script'          => 'gpb-' . $this->block_name . '-frontend',
				'supports'        => [],
			)
		);
	}

	/**
	 * Render the Product LookBook
	 *
	 * @param array  $attributes Block attributes. Default empty array.
	 * @param string $content    Block content. Default empty string.
	 * @return string Rendered block type output.
	 */
	public function render( $attributes = array(), $content = '' ) {
		$media_id = isset( $attributes['mediaId'] ) ? (int) $attributes['mediaId'] : 0;
		$attributes = wp_parse_args( $attributes, $this->defaults );
		if ( ! $attributes['height'] ) {
			$attributes['height'] = wc_get_theme_support( 'featured_block::default_height', 500 );
		}

		$output = sprintf(
			'<div class="%1$s" style="%2$s"><div class="wc-block-lookbook__wrapper">%3$s</div></div>',
			esc_attr( $this->get_classes( $attributes ) ),
			esc_attr( $this->get_styles( $attributes ) ),
			$this->get_points_html( $attributes )
		);

		return $output;
	}

	/**
	 * Get class names for the block container.
	 *
	 * @param array $attributes Block attributes. Default empty array.
	 * @return string
	 */
	public function get_classes( $attributes ) {
		$classes = array( 'wc-block-' . $this->block_name );

		if ( isset( $attributes['align'] ) ) {
			$classes[] = "align{$attributes['align']}";
		}

		if ( isset( $attributes['dimRatio'] ) && ( 0 !== $attributes['dimRatio'] ) ) {
			$classes[] = 'has-background-dim';

			if ( 50 !== $attributes['dimRatio'] ) {
				$classes[] = 'has-background-dim-' . 10 * round( $attributes['dimRatio'] / 10 );
			}
		}

		if ( isset( $attributes['overlayColor'] ) ) {
			$classes[] = "has-{$attributes['overlayColor']}-background-color";
		}

		if ( isset( $attributes['className'] ) ) {
			$classes[] = $attributes['className'];
		}

		return implode( ' ', $classes );
	}

	/**
	 * Get the styles for the wrapper element (background image, color).
	 *
	 * @param array    $attributes Block attributes. Default empty array.
	 * @return string
	 */
	public function get_styles( $attributes ) {
		$style      = '';
		$image_size = 'large';
		if ( 'none' !== $attributes['align'] || $attributes['height'] > 800 ) {
			$image_size = 'full';
		}

		if ( $attributes['mediaId'] ) {
			$image = wp_get_attachment_image_url( $attributes['mediaId'], $image_size );
		}

		if ( ! isset( $image ) && ! empty( $attributes['mediaSrc'] ) ) {
			$image = $attributes['mediaSrc'];
		}

		if ( ! empty( $image ) ) {
			$style .= sprintf( 'background-image:url(%s);', esc_url( $image ) );
			$style .= 'background-size: cover;';
		}

		if ( isset( $attributes['customOverlayColor'] ) ) {
			$style .= sprintf( 'background-color:%s;', esc_attr( $attributes['customOverlayColor'] ) );
		}

		if ( isset( $attributes['height'] ) ) {
			$style .= sprintf( 'min-height:%dpx;', intval( $attributes['height'] ) );
		}

		if ( is_array( $attributes['focalPoint'] ) && 2 === count( $attributes['focalPoint'] ) ) {
			$style .= sprintf(
				'background-position: %s%% %s%%',
				$attributes['focalPoint']['x'] * 100,
				$attributes['focalPoint']['y'] * 100
			);
		}

		return $style;
	}

	/**
	 * retreive points html
	 *
	 * @param array $attributess
	 * @since 1.0.0
	 */
	public function get_points_html( $attributes ) {
		$blockId = ! empty( $attributes['blockId'] ) ? $attributes['blockId'] : '';
		$points = ! empty( $attributes['points'] ) ? $attributes['points'] : array();

		$html = array();
		foreach ( $points as $index => $point ) {
			if ( ! isset( $point['productId'] ) || ! $point['productId'] || $point['x'] === '' || $point['y'] === '' ) {
				continue;
			}
			$html[] = sprintf(
				'<div id="%1$s" class="%2$s" style="%3$s" data-product-id="%4$d">%5$s</div>',
				esc_attr( 'lookbook-item-' . $blockId . '-' . $index . '-' . $point['productId'] ),
				esc_attr( 'wc-product-lookbook-item' . ( isset( $point['enableIcon'] ) && $point['enableIcon'] ? ' has-icon' : '' ) ),
				esc_attr( 'left: ' . ($point['x'] * 100) . '%; top: ' . ($point['y'] * 100) . '%' ),
				esc_attr( $point['productId'] ),
				! empty( $point['icon'] ) ? gtg_render_svg_html( $point['icon'] ) : ''
			);
		}

		return implode( '', $html );
	}

	public function render_product( $id ) {
		ob_start();
		$GLOBALS['post'] = get_post( $id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $GLOBALS['post'] );

		// Render product template.
		wc_get_template_part( 'content', 'product' );
		return ob_get_clean();
	}


}
