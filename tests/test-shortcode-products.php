<?php
/**
 * Tests for shortcode filter functions.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\add_products_shortcode_attribute_hook;
use function WC_Outlet\filter_products_shortcode_query_hook;
use function WC_Outlet\max_price_posts_clauses;
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

	// MAX PRICE FILTERING TESTS.

	public function test_max_price_added_to_query_args_when_get_param_present(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$_GET['max_price'] = '100';
		$query_args        = array( 'post_type' => 'product' );
		$attributes        = array( 'wc_outlet' => true );

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertArrayHasKey( 'wc_outlet_max_price', $result );
		$this->assertSame( 100, $result['wc_outlet_max_price'] );

		// Cleanup.
		unset( $_GET['max_price'] );
	}

	public function test_max_price_not_added_when_get_param_absent(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$query_args = array( 'post_type' => 'product' );
		$attributes = array( 'wc_outlet' => true );

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertArrayNotHasKey( 'wc_outlet_max_price', $result );
	}

	public function test_max_price_not_added_when_wc_outlet_not_enabled(): void {
		// Arrange.
		$_GET['max_price'] = '100';
		$query_args        = array( 'post_type' => 'product' );
		$attributes        = array();

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertArrayNotHasKey( 'wc_outlet_max_price', $result );

		// Cleanup.
		unset( $_GET['max_price'] );
	}

	public function test_max_price_sanitizes_invalid_string(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$_GET['max_price'] = 'invalid';
		$query_args        = array( 'post_type' => 'product' );
		$attributes        = array( 'wc_outlet' => true );

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertArrayHasKey( 'wc_outlet_max_price', $result );
		$this->assertNull( $result['wc_outlet_max_price'] );

		// Cleanup.
		unset( $_GET['max_price'] );
	}

	public function test_max_price_sanitizes_negative_value(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$_GET['max_price'] = '-50';
		$query_args        = array( 'post_type' => 'product' );
		$attributes        = array( 'wc_outlet' => true );

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertArrayHasKey( 'wc_outlet_max_price', $result );
		$this->assertNull( $result['wc_outlet_max_price'] );

		// Cleanup.
		unset( $_GET['max_price'] );
	}

	public function test_max_price_sanitizes_decimal_value(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$_GET['max_price'] = '99.99';
		$query_args        = array( 'post_type' => 'product' );
		$attributes        = array( 'wc_outlet' => true );

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertArrayHasKey( 'wc_outlet_max_price', $result );
		$this->assertNull( $result['wc_outlet_max_price'] );

		// Cleanup.
		unset( $_GET['max_price'] );
	}

	public function test_max_price_accepts_zero(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$_GET['max_price'] = '0';
		$query_args        = array( 'post_type' => 'product' );
		$attributes        = array( 'wc_outlet' => true );

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertArrayHasKey( 'wc_outlet_max_price', $result );
		$this->assertSame( 0, $result['wc_outlet_max_price'] );

		// Cleanup.
		unset( $_GET['max_price'] );
	}

	public function test_max_price_accepts_large_integer(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		$_GET['max_price'] = '999999';
		$query_args        = array( 'post_type' => 'product' );
		$attributes        = array( 'wc_outlet' => true );

		// Act.
		$result = filter_products_shortcode_query_hook( $query_args, $attributes, 'products' );

		// Assert.
		$this->assertArrayHasKey( 'wc_outlet_max_price', $result );
		$this->assertSame( 999999, $result['wc_outlet_max_price'] );

		// Cleanup.
		unset( $_GET['max_price'] );
	}

	// MAX PRICE POSTS CLAUSES TESTS.

	public function test_max_price_posts_clauses_unchanged_when_max_price_not_set(): void {
		// Arrange.
		$clauses = array(
			'where' => ' WHERE 1=1',
			'join'  => '',
		);
		$query   = new WP_Query();

		// Act.
		$result = max_price_posts_clauses( $clauses, $query );

		// Assert.
		$this->assertSame( $clauses, $result );
	}

	public function test_max_price_posts_clauses_adds_join_and_where_clause(): void {
		// Arrange.
		global $wpdb;
		$clauses = array(
			'where' => ' WHERE 1=1',
			'join'  => '',
		);
		$query   = new WP_Query();
		$query->set( 'wc_outlet_max_price', 50 );

		// Act.
		$result = max_price_posts_clauses( $clauses, $query );

		// Assert.
		$this->assertStringContainsString( 'wc_product_meta_lookup', $result['join'] );
		$this->assertStringContainsString( 'LEFT JOIN', $result['join'] );
		$this->assertStringContainsString( 'AND NOT ( 50 < wc_product_meta_lookup.min_price )', $result['where'] );
	}

	public function test_max_price_posts_clauses_does_not_duplicate_join(): void {
		// Arrange.
		global $wpdb;

		$clauses = array(
			'where' => ' WHERE 1=1',
			'join'  => " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON {$wpdb->posts}.ID = wc_product_meta_lookup.product_id ",
		);

		$query = new WP_Query();
		$query->set( 'wc_outlet_max_price', 75 );

		// Act.
		$result = max_price_posts_clauses( $clauses, $query );

		// Assert.
		$this->assertSame(
			$clauses['join'],
			$result['join'],
			'Existing join should not be modified'
		);

		$this->assertStringContainsString(
			'AND NOT ( 75 < wc_product_meta_lookup.min_price )',
			$result['where']
		);
	}

	public function test_max_price_posts_clauses_unchanged_when_max_price_is_null(): void {
		// Arrange.
		$clauses = array(
			'where' => ' WHERE 1=1',
			'join'  => '',
		);
		$query   = new WP_Query();
		$query->set( 'wc_outlet_max_price', null );

		// Act.
		$result = max_price_posts_clauses( $clauses, $query );

		// Assert.
		$this->assertSame( $clauses, $result );
	}

	public function test_max_price_posts_clauses_with_zero_price(): void {
		// Arrange.
		global $wpdb;
		$clauses = array(
			'where' => ' WHERE 1=1',
			'join'  => '',
		);
		$query   = new WP_Query();
		$query->set( 'wc_outlet_max_price', 0 );

		// Act.
		$result = max_price_posts_clauses( $clauses, $query );

		// Assert.
		$this->assertStringContainsString( 'wc_product_meta_lookup', $result['join'] );
		$this->assertStringContainsString( 'AND NOT ( 0 < wc_product_meta_lookup.min_price )', $result['where'] );
	}

	public function test_max_price_posts_clauses_with_large_price(): void {
		// Arrange.
		global $wpdb;
		$clauses = array(
			'where' => ' WHERE 1=1',
			'join'  => '',
		);
		$query   = new WP_Query();
		$query->set( 'wc_outlet_max_price', 999999 );

		// Act.
		$result = max_price_posts_clauses( $clauses, $query );

		// Assert.
		$this->assertStringContainsString( 'wc_product_meta_lookup', $result['join'] );
		$this->assertStringContainsString( 'AND NOT ( 999999 < wc_product_meta_lookup.min_price )', $result['where'] );
	}
}
