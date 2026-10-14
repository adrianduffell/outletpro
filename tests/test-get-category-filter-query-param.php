<?php
/**
 * Tests for get_category_filter_query_param().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_category_filter_query_param;

class Test_Get_Category_Filter_Query_Param extends WP_UnitTestCase {

	public function test_requested_outlet_category_slug_is_sanitized(): void {
		// Arrange.
		set_query_var( 'outlet_category', 'Clothing & Shoes' );

		// Act.
		$result = get_category_filter_query_param();

		// Assert.
		$this->assertSame( 'clothing-shoes', $result );

		// Cleanup.
		set_query_var( 'outlet_category', '' );
	}

	public function test_requested_outlet_category_slug_ignores_non_string_value(): void {
		// Arrange.
		set_query_var( 'outlet_category', array( 'clothing' ) );

		// Act.
		$result = get_category_filter_query_param();

		// Assert.
		$this->assertNull( $result );

		// Cleanup.
		set_query_var( 'outlet_category', '' );
	}
	public function test_missing_category_query_param_returns_null(): void {
		// Arrange.
		set_query_var( 'outlet_category', '' );

		// Act.
		$result = get_category_filter_query_param();

		// Assert.
		$this->assertNull( $result );
	}
}
