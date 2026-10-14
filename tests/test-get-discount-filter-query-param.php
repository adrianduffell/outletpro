<?php
/**
 * Tests for get_discount_filter_query_param().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_discount_filter_query_param;
use function OutletPro\init_taxonomies;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Get_Discount_Filter_Query_Param extends WP_UnitTestCase {

	public function test_array_discount_is_ignored(): void {
		// Arrange.
		init_taxonomies();
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		set_query_var( 'outlet_discount', array( '50' ) );

		// Act.
		$result = get_discount_filter_query_param();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_text_discount_is_ignored(): void {
		// Arrange.
		init_taxonomies();
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		set_query_var( 'outlet_discount', 'no' );

		// Act.
		$result = get_discount_filter_query_param();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_fraction_discount_is_ignored(): void {
		// Arrange.
		init_taxonomies();
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		set_query_var( 'outlet_discount', '50.5' );

		// Act.
		$result = get_discount_filter_query_param();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_zero_discount_is_ignored(): void {
		// Arrange.
		init_taxonomies();
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		set_query_var( 'outlet_discount', '0' );

		// Act.
		$result = get_discount_filter_query_param();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_out_of_range_discount_is_ignored(): void {
		// Arrange.
		init_taxonomies();
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		set_query_var( 'outlet_discount', '101' );

		// Act.
		$result = get_discount_filter_query_param();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_unknown_discount_is_ignored(): void {
		// Arrange.
		init_taxonomies();
		wp_insert_term( 'outlet-discount-50', OUTLET_SEARCH_INDEX_TAXONOMY );
		set_query_var( 'outlet_discount', '49' );

		// Act.
		$result = get_discount_filter_query_param();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_missing_discount_is_ignored(): void {
		// Arrange.
		set_query_var( 'outlet_discount', '' );

		// Act.
		$result = get_discount_filter_query_param();

		// Assert.
		$this->assertNull( $result );
	}
	public function test_existing_discount_facet_is_returned(): void {
		// Arrange.
		init_taxonomies();
		wp_insert_term( 'outlet-discount-70', OUTLET_SEARCH_INDEX_TAXONOMY );
		set_query_var( 'outlet_discount', '70' );

		// Act.
		$result = get_discount_filter_query_param();

		// Assert.
		$this->assertSame( 'outlet-discount-70', $result );
	}
}
