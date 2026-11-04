<?php
/**
 * Tests for single_product_template_has_block().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\single_product_template_has_block;

class Test_Single_Product_Template_Has_Block extends WP_UnitTestCase {

	public function test_finds_product_meta_in_the_template(): void {
		// Arrange.
		$template_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'single-product',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:group --><div class="wp-block-group"><!-- wp:woocommerce/product-meta /--></div><!-- /wp:group -->',
			)
		);
		wp_set_object_terms( $template_id, get_stylesheet(), 'wp_theme' );

		// Act.
		$result = single_product_template_has_block( 'woocommerce/product-meta' );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_finds_product_meta_in_a_referenced_pattern(): void {
		// Arrange.
		register_block_pattern(
			'outletpro/test-product-meta',
			array(
				'title'   => 'Product meta',
				'content' => '<!-- wp:woocommerce/product-meta /-->',
			)
		);
		$template_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'single-product',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:pattern {"slug":"outletpro/test-product-meta"} /-->',
			)
		);
		wp_set_object_terms( $template_id, get_stylesheet(), 'wp_theme' );

		// Act.
		$result = single_product_template_has_block( 'woocommerce/product-meta' );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_false_when_product_meta_is_missing(): void {
		// Arrange.
		$template_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'single-product',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:woocommerce/product-details /-->',
			)
		);
		wp_set_object_terms( $template_id, get_stylesheet(), 'wp_theme' );

		// Act.
		$result = single_product_template_has_block( 'woocommerce/product-meta' );

		// Assert.
		$this->assertFalse( $result );
	}
}
