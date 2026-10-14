<?php
/**
 * Tests for get_price_filter_query_param().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_price_filter_query_param;
use function OutletPro\init_taxonomies;
use const OutletPro\OUTLET_SEARCH_INDEX_TAXONOMY;

class Test_Get_Price_Filter_Query_Param extends WP_UnitTestCase {

	public function test_unknown_price_facet_is_ignored(): void {
		// Arrange.
		init_taxonomies();
		set_query_var( 'outlet_price', '49' );

		// Act.
		$arg = get_price_filter_query_param();

		// Assert.
		$this->assertNull( $arg );
	}

	public function test_malformed_price_facet_is_ignored(): void {
		// Arrange.
		set_query_var( 'outlet_price', array( '50' ) );

		// Act.
		$arg = get_price_filter_query_param();

		// Assert.
		$this->assertNull( $arg );
	}
	public function test_existing_price_facet_is_returned(): void {
		// Arrange.
		init_taxonomies();
		wp_insert_term( 'outlet-price-250', OUTLET_SEARCH_INDEX_TAXONOMY );
		set_query_var( 'outlet_price', '250' );

		// Act.
		$result = get_price_filter_query_param();

		// Assert.
		$this->assertSame( 'outlet-price-250', $result );
	}
}
