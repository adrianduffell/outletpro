<?php
/**
 * Tests for register_outlet_products_pattern().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\init_patterns;
use function WC_Outlet\register_outlet_products_pattern;

class Test_Register_Outlet_Products_Pattern extends WP_UnitTestCase {

	public function test_pattern_is_registered(): void {
		// Arrange.
		unregister_block_pattern( 'wc-outlet/outlet-products' );

		// Act.
		register_outlet_products_pattern();

		// Assert.
		$this->assertNotNull( \WP_Block_Patterns_Registry::get_instance()->get_registered( 'wc-outlet/outlet-products' ) );
	}

	public function test_pattern_title_is_outlet_products(): void {
		// Arrange.
		unregister_block_pattern( 'wc-outlet/outlet-products' );

		// Act.
		register_outlet_products_pattern();

		// Assert.
		$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'wc-outlet/outlet-products' );
		$this->assertSame( 'Outlet products', $pattern['title'] );
	}

	public function test_pattern_content_includes_product_collection_block(): void {
		// Arrange.
		unregister_block_pattern( 'wc-outlet/outlet-products' );

		// Act.
		register_outlet_products_pattern();

		// Assert.
		$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'wc-outlet/outlet-products' );
		$this->assertStringContainsString( 'wp:woocommerce/product-collection', $pattern['content'] );
	}

	public function test_pattern_content_includes_wc_outlet_query_flag(): void {
		// Arrange.
		unregister_block_pattern( 'wc-outlet/outlet-products' );

		// Act.
		register_outlet_products_pattern();

		// Assert.
		$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'wc-outlet/outlet-products' );
		$this->assertStringContainsString( '"wc_outlet":true', $pattern['content'] );
	}

	public function test_pattern_is_registered_by_init_patterns(): void {
		// Arrange.
		unregister_block_pattern( 'wc-outlet/outlet-products' );

		// Act.
		init_patterns();

		// Assert.
		$this->assertNotNull( \WP_Block_Patterns_Registry::get_instance()->get_registered( 'wc-outlet/outlet-products' ) );
	}
}
