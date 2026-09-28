<?php
/**
 * Tests for is_single_product_context().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\is_single_product_context;

class Test_Is_Single_Product_Context extends WP_UnitTestCase {

	public function test_single_product_block_template_is_recognized(): void {
		// Arrange.
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = is_single_product_context( $template );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_hidden_single_product_block_template_is_recognized(): void {
		// Arrange.
		$template       = new WP_Block_Template();
		$template->slug = 'hidden-single-product';

		// Act.
		$result = is_single_product_context( $template );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_single_product_block_template_slug_variation_is_recognized(): void {
		// Arrange.
		$template       = new WP_Block_Template();
		$template->slug = 'single-product-design';

		// Act.
		$result = is_single_product_context( $template );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_other_block_template_is_not_recognized(): void {
		// Arrange.
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = is_single_product_context( $template );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_namespaced_hidden_single_product_pattern_is_recognized(): void {
		// Arrange.
		$context = array( 'name' => 'purple/hidden-single-product' );

		// Act.
		$result = is_single_product_context( $context );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_child_theme_hidden_single_product_pattern_is_recognized(): void {
		// Arrange.
		$context = array( 'name' => 'purple-child/hidden-single-product' );

		// Act.
		$result = is_single_product_context( $context );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_single_product_pattern_is_recognized(): void {
		// Arrange.
		$context = array( 'name' => 'purple-foo/single-product' );

		// Act.
		$result = is_single_product_context( $context );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_single_product_pattern_name_variation_is_recognized(): void {
		// Arrange.
		$context = array( 'name' => 'purple-bar/single-product-design' );

		// Act.
		$result = is_single_product_context( $context );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_other_theme_hidden_single_product_pattern_is_recognized(): void {
		// Arrange.
		$context = array( 'name' => 'red/hidden-single-product' );

		// Act.
		$result = is_single_product_context( $context );

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_other_pattern_is_not_recognized(): void {
		// Arrange.
		$context = array( 'name' => 'purple/product-collection' );

		// Act.
		$result = is_single_product_context( $context );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_pattern_without_name_is_not_recognized(): void {
		// Arrange.
		$context = array();

		// Act.
		$result = is_single_product_context( $context );

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_null_context_is_not_recognized(): void {
		// Arrange.
		$context = null;

		// Act.
		$result = is_single_product_context( $context );

		// Assert.
		$this->assertFalse( $result );
	}
}
