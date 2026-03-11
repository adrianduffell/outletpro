<?php
/**
 * Tests for shortcode filter functions.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\hook_add_products_shortcode_attribute;
use function WC_Clearance\hook_filter_products_shortcode_query;
use function WC_Clearance\register_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Shortcode_Products extends WP_UnitTestCase {

	public function test_query_unchanged_when_is_clearance_attribute_absent(): void {
		// Arrange.
		$query_args = array( 'post_type' => 'product' );
		$attributes = array();

		// Act.
		$result = hook_filter_products_shortcode_query( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertSame( $query_args, $result );
	}

	public function test_query_unchanged_when_is_clearance_is_false(): void {
		// Arrange.
		$query_args = array( 'post_type' => 'product' );
		$attributes = array( 'is_clearance' => false );

		// Act.
		$result = hook_filter_products_shortcode_query( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertSame( $query_args, $result );
	}

	public function test_query_gets_tax_query_when_is_clearance_is_true(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$query_args = array( 'post_type' => 'product' );
		$attributes = array( 'is_clearance' => true );

		// Act.
		$result = hook_filter_products_shortcode_query( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertCount( 1, $result['tax_query'] );
		$this->assertSame( CLEARANCE_STATUS_TAXONOMY, $result['tax_query'][0]['taxonomy'] );
		$this->assertSame( CLEARANCE_STATUS_CANONICAL_TERM, $result['tax_query'][0]['terms'] );
	}

	public function test_tax_query_appended_when_existing_tax_query_present(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$existing_tax = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => 'sale',
		);
		$query_args   = array(
			'post_type' => 'product',
			'tax_query' => array( $existing_tax ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		);
		$attributes   = array( 'is_clearance' => true );

		// Act.
		$result = hook_filter_products_shortcode_query( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertCount( 2, $result['tax_query'] );
		$this->assertSame( $existing_tax, $result['tax_query'][0] );
		$this->assertSame( CLEARANCE_STATUS_TAXONOMY, $result['tax_query'][1]['taxonomy'] );
	}

	public function test_hook_add_products_shortcode_attribute_adds_is_clearance_when_present(): void {
		// Arrange.
		$out  = array();
		$atts = array( 'is_clearance' => 'yes' );

		// Act.
		$result = hook_add_products_shortcode_attribute( $out, array(), $atts );

		// Assert.
		$this->assertArrayHasKey( 'is_clearance', $result );
		$this->assertTrue( $result['is_clearance'] );
	}

	public function test_hook_add_products_shortcode_attribute_converts_false_string(): void {
		// Arrange.
		$out  = array();
		$atts = array( 'is_clearance' => 'no' );

		// Act.
		$result = hook_add_products_shortcode_attribute( $out, array(), $atts );

		// Assert.
		$this->assertArrayHasKey( 'is_clearance', $result );
		$this->assertFalse( $result['is_clearance'] );
	}

	public function test_hook_add_products_shortcode_attribute_unchanged_when_is_clearance_absent(): void {
		// Arrange.
		$out  = array( 'limit' => 12 );
		$atts = array();

		// Act.
		$result = hook_add_products_shortcode_attribute( $out, array(), $atts );

		// Assert.
		$this->assertArrayNotHasKey( 'is_clearance', $result );
		$this->assertSame( $out, $result );
	}
}
