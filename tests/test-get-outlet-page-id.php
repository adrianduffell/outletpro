<?php
/**
 * Test the get_outlet_page_id function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\get_outlet_page_id;
use const WC_Outlet\OUTLET_PAGE_OPTION;

class Test_Get_Outlet_Page_Id extends WP_UnitTestCase {

	public function test_returns_int_when_option_is_positive_int(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 42 );

		// Act.
		$result = get_outlet_page_id();

		// Assert.
		$this->assertSame( 42, $result );
	}

	public function test_returns_int_when_option_is_numeric_string(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, '42' );

		// Act.
		$result = get_outlet_page_id();

		// Assert.
		$this->assertSame( 42, $result );
	}

	public function test_returns_null_when_option_is_not_set(): void {
		// Arrange.
		delete_option( OUTLET_PAGE_OPTION );

		// Act.
		$result = get_outlet_page_id();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_throws_when_option_is_zero(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 0 );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_page_id();
	}

	public function test_throws_when_option_is_zero_string(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, '0' );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_page_id();
	}

	public function test_throws_when_option_is_non_numeric_string(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 'not-an-int' );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_page_id();
	}

	public function test_throws_when_option_is_negative_int(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, -1 );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_page_id();
	}

	public function test_throws_when_option_is_negative_string(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, '-1' );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_page_id();
	}

	public function test_throws_when_option_is_empty_string(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, '' );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_page_id();
	}

	public function test_throws_when_option_is_float(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, 1.5 );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_page_id();
	}

	public function test_throws_when_option_is_array(): void {
		// Arrange.
		update_option( OUTLET_PAGE_OPTION, array( 1, 2, 3 ) );

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_page_id();
	}
}
