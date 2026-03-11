<?php
/**
 * Tests for shortcode filter functions.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_products_shortcode_attribute;
use function WC_Clearance\filter_products_shortcode_query;
use function WC_Clearance\register_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Shortcode_Products extends WP_UnitTestCase {

	public function test_query_unchanged_when_on_clearance_attribute_absent(): void {
		// Arrange.
		$query_args = array( 'post_type' => 'product' );
		$attributes = array();

		// Act.
		$result = filter_products_shortcode_query( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertSame( $query_args, $result );
	}

	public function test_query_unchanged_when_on_clearance_is_false(): void {
		// Arrange.
		$query_args = array( 'post_type' => 'product' );
		$attributes = array( 'on_clearance' => false );

		// Act.
		$result = filter_products_shortcode_query( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertSame( $query_args, $result );
	}

	public function test_query_gets_tax_query_when_on_clearance_is_true(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$query_args = array( 'post_type' => 'product' );
		$attributes = array( 'on_clearance' => true );

		// Act.
		$result = filter_products_shortcode_query( $query_args, $attributes, 'products' );

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
		$attributes   = array( 'on_clearance' => true );

		// Act.
		$result = filter_products_shortcode_query( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertCount( 2, $result['tax_query'] );
		$this->assertSame( $existing_tax, $result['tax_query'][0] );
		$this->assertSame( CLEARANCE_STATUS_TAXONOMY, $result['tax_query'][1]['taxonomy'] );
	}

	public function test_add_products_shortcode_attribute_adds_on_clearance_when_present(): void {
		// Arrange.
		$out  = array();
		$atts = array( 'on_clearance' => 'yes' );

		// Act.
		$result = add_products_shortcode_attribute( $out, array(), $atts );

		// Assert.
		$this->assertArrayHasKey( 'on_clearance', $result );
		$this->assertTrue( $result['on_clearance'] );
	}

	public function test_add_products_shortcode_attribute_converts_false_string(): void {
		// Arrange.
		$out  = array();
		$atts = array( 'on_clearance' => 'no' );

		// Act.
		$result = add_products_shortcode_attribute( $out, array(), $atts );

		// Assert.
		$this->assertArrayHasKey( 'on_clearance', $result );
		$this->assertFalse( $result['on_clearance'] );
	}

	public function test_add_products_shortcode_attribute_unchanged_when_on_clearance_absent(): void {
		// Arrange.
		$out  = array( 'limit' => 12 );
		$atts = array();

		// Act.
		$result = add_products_shortcode_attribute( $out, array(), $atts );

		// Assert.
		$this->assertArrayNotHasKey( 'on_clearance', $result );
		$this->assertSame( $out, $result );
	}
}
