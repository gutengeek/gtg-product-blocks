<?php
namespace GPB\BlockTypes;

use \Automattic\WooCommerce\Blocks\BlockTypes\FeaturedCategory as BaseFeaturedCategory;

defined( 'ABSPATH' ) || exit;

class FeaturedCategory extends BaseFeaturedCategory {

	/**
	 * Default attribute values, should match what's set in JS `registerBlockType`.
	 *
	 * @var array
	 */
	protected $defaults = array(
		'align'        => 'none',
		'contentAlign' => 'center',
		'verticalAlign' => '',
		'dimRatio'     => 50,
		'focalPoint'   => false,
		'height'       => false,
		'mediaId'      => 0,
		'mediaSrc'     => '',
		'showDesc'     => true,
	);

	/**
	 * get attributes override
	 *
	 * @return array
	 */
	public function get_attributes_override() {
		return array(
			'titleTypography' => array(
				'type' => 'object',
				'default' => array(),
				'style' => [
					[
						'selector' => '{{GUTENGEEK}} .wc-block-featured-category__title'
					]
				]
			),
			'descTypography' => array(
				'type' => 'object',
				'default' => array(),
				'style' => [
					[
						'selector' => '{{GUTENGEEK}} .wc-block-featured-category__description'
					]
				]
			)
		);
	}

	/**
	 * Render the Featured Category block.
	 *
	 * @param array  $attributes Block attributes. Default empty array.
	 * @param string $content    Block content. Default empty string.
	 * @return string Rendered block type output.
	 */
	public function render( $attributes = array(), $content = '' ) {
		$blockId = isset( $attributes['blockId'] ) ? $attributes['blockId'] : '';
		$id       = isset( $attributes['categoryId'] ) ? (int) $attributes['categoryId'] : 0;
		$category = get_term( $id, 'product_cat' );
		if ( ! $category || is_wp_error( $category ) ) {
			return '';
		}
		$attributes = wp_parse_args( $attributes, $this->defaults );
		if ( ! $attributes['height'] ) {
			$attributes['height'] = wc_get_theme_support( 'featured_block::default_height', 500 );
		}

		$title = sprintf(
			'<h2 class="wc-block-featured-category__title">%s</h2>',
			wp_kses_post( $category->name )
		);

		$desc_str = sprintf(
			'<div class="wc-block-featured-category__description">%s</div>',
			wc_format_content( $category->description )
		);

		$output = sprintf(
			'<div id="%1$s" class="%2$s" style="%3$s"><div class="wc-block-featured-category__wrapper">',
			esc_attr( 'gutengeek-block-' . $blockId ),
			esc_attr( $this->get_classes( $attributes ) ),
			esc_attr( $this->get_styles( $attributes, $category ) )
		);

		$output .= $title;
		if ( $attributes['showDesc'] ) {
			$output .= $desc_str;
		}
		$output .= '<div class="wc-block-featured-category__link">' . $content . '</div>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Get class names for the block container.
	 *
	 * @param array $attributes Block attributes. Default empty array.
	 * @return string
	 */
	public function get_classes( $attributes ) {
		$classes = array();
		$classes[] = parent::get_classes( $attributes );
		if ( ! empty( $attributes['verticalAlign'] ) ) {
			$classes[] = 'has-vertical-' . $attributes['verticalAlign'];
		}
		return implode( ' ', $classes );
	}

}
