<?php
/**
 * Tests for public Outlet filter query variables.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_query_vars;
use function OutletPro\init_query_vars;

class Test_Register_Outlet_Query_Vars_Hook extends WP_UnitTestCase {

	public function test_outlet_filters_are_registered_without_losing_existing_vars(): void {
		// Arrange.
		init_query_vars();

		// Act.
		$vars = apply_filters( 'query_vars', array( 'existing' ) );

		// Assert.
		$this->assertContains( 'existing', $vars );
		$this->assertContains( 'outlet_category', $vars );
		$this->assertContains( 'outlet_price', $vars );
		$this->assertContains( 'outlet_discount', $vars );
	}

	public function test_url_filters_are_available_as_wordpress_query_vars(): void {
		// Arrange.
		init_query_vars();

		// Act.
		$this->go_to( '/?outlet_category=shirts&outlet_price=25&outlet_discount=70' );

		// Assert.
		$this->assertSame( 'shirts', get_query_var( 'outlet_category' ) );
		$this->assertSame( '25', get_query_var( 'outlet_price' ) );
		$this->assertSame( '70', get_query_var( 'outlet_discount' ) );
	}
	public function test_deinit_removes_outlet_query_variables(): void {
		// Arrange.
		deinit_query_vars();
		init_query_vars();

		// Act.
		deinit_query_vars();
		$vars = apply_filters( 'query_vars', array( 'existing' ) );

		// Assert.
		$this->assertContains( 'existing', $vars );
		$this->assertNotContains( 'outlet_category', $vars );
		$this->assertNotContains( 'outlet_price', $vars );
		$this->assertNotContains( 'outlet_discount', $vars );
	}
}
