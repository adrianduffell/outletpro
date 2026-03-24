<?php
/**
 * Tests for woocommerce_rest_product_object_query_hook function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\woocommerce_rest_product_object_query_hook;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Woocommerce_Rest_Product_Object_Query_Hook extends WP_UnitTestCase {

	public function test_query_unchanged_when_clearance_status_is_absent(): void {
		// Arrange.
		$args    = array( 'post_type' => 'product' );
		$request = new WP_REST_Request();

		// Act.
		$result = woocommerce_rest_product_object_query_hook( $args, $request );

		// Assert.
		$this->assertSame( $args, $result );
	}

	public function test_query_unchanged_when_clearance_status_is_empty_string(): void {
		// Arrange.
		$args    = array( 'post_type' => 'product' );
		$request = new WP_REST_Request();
		$request->set_param( 'wc_clearance_status', '' );

		// Act.
		$result = woocommerce_rest_product_object_query_hook( $args, $request );

		// Assert.
		$this->assertSame( $args, $result );
	}

	public function test_adds_tax_query_when_clearance_status_is_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$args    = array( 'post_type' => 'product' );
		$request = new WP_REST_Request();
		$request->set_param( 'wc_clearance_status', CLEARANCE_STATUS_CANONICAL_TERM );

		// Act.
		$result = woocommerce_rest_product_object_query_hook( $args, $request );

		// Assert.
		$this->assertArrayHasKey( 'tax_query', $result );
		$this->assertCount( 1, $result['tax_query'] );
		$this->assertSame( CLEARANCE_STATUS_TAXONOMY, $result['tax_query'][0]['taxonomy'] );
		$this->assertSame( array( CLEARANCE_STATUS_CANONICAL_TERM ), $result['tax_query'][0]['terms'] );
	}

	public function test_tax_query_appended_when_existing_tax_query_present(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$existing_tax = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array( 'sale' ),
		);
		$args         = array(
			'post_type' => 'product',
			'tax_query' => array( $existing_tax ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		);
		$request      = new WP_REST_Request();
		$request->set_param( 'wc_clearance_status', CLEARANCE_STATUS_CANONICAL_TERM );

		// Act.
		$result = woocommerce_rest_product_object_query_hook( $args, $request );

		// Assert.
		$this->assertCount( 2, $result['tax_query'] );
		$this->assertSame( $existing_tax, $result['tax_query'][0] );
		$this->assertSame( CLEARANCE_STATUS_TAXONOMY, $result['tax_query'][1]['taxonomy'] );
	}
}
