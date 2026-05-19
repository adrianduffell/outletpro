<?php
/**
 * Tests for shortcode filter functions.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\add_products_shortcode_attribute_hook;
use function WC_Outlet\filter_products_shortcode_query_hook;
use function WC_Outlet\register_outlet_status_taxonomy;
use const WC_Outlet\OUTLET_STATUS_CANONICAL_TERM;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;

class Test_Shortcode_Products extends WP_UnitTestCase {

	public function test_query_unchanged_when_wc_outlet_attribute_absent(): void {
		// Arrange.
		$query_args = array( 'post_type' => 'product' );
		$attributes = array();

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertSame( $query_args, $result );
	}

	public function test_query_unchanged_when_wc_outlet_is_false(): void {
		// Arrange.
		$query_args = array( 'post_type' => 'product' );
		$attributes = array( 'wc_outlet' => false );

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertSame( $query_args, $result );
	}

	public function test_query_gets_tax_query_when_wc_outlet_is_true(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$query_args = array( 'post_type' => 'product' );
		$attributes = array( 'wc_outlet' => true );

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertCount( 1, $result['tax_query'] );
		$this->assertSame( OUTLET_STATUS_TAXONOMY, $result['tax_query'][0]['taxonomy'] );
		$this->assertSame( OUTLET_STATUS_CANONICAL_TERM, $result['tax_query'][0]['terms'] );
	}

	public function test_tax_query_appended_when_existing_tax_query_present(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$existing_tax = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => 'sale',
		);
		$query_args   = array(
			'post_type' => 'product',
			'tax_query' => array( $existing_tax ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		);
		$attributes   = array( 'wc_outlet' => true );

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertCount( 2, $result['tax_query'] );
		$this->assertSame( $existing_tax, $result['tax_query'][0] );
		$this->assertSame( OUTLET_STATUS_TAXONOMY, $result['tax_query'][1]['taxonomy'] );
	}

	public function test_add_products_shortcode_attribute_hook_adds_wc_outlet_when_present(): void {
		// Arrange.
		$out  = array();
		$atts = array( 'wc_outlet' => 'yes' );

		// Act.
		$result = add_products_shortcode_attribute_hook( $out, array(), $atts );

		// Assert.
		$this->assertArrayHasKey( 'wc_outlet', $result );
		$this->assertTrue( $result['wc_outlet'] );
	}

	public function test_add_products_shortcode_attribute_hook_converts_false_string(): void {
		// Arrange.
		$out  = array();
		$atts = array( 'wc_outlet' => 'no' );

		// Act.
		$result = add_products_shortcode_attribute_hook( $out, array(), $atts );

		// Assert.
		$this->assertArrayHasKey( 'wc_outlet', $result );
		$this->assertFalse( $result['wc_outlet'] );
	}

	public function test_add_products_shortcode_attribute_hook_unchanged_when_wc_outlet_absent(): void {
		// Arrange.
		$out  = array( 'limit' => 12 );
		$atts = array();

		// Act.
		$result = add_products_shortcode_attribute_hook( $out, array(), $atts );

		// Assert.
		$this->assertArrayNotHasKey( 'wc_outlet', $result );
		$this->assertSame( $out, $result );
	}
}
