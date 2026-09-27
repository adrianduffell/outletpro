<?php
/**
 * Test the get_outlet_badge_label function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_outlet_badge_label;
use function OutletPro\register_outlet_badge_label_setting;
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;

class Test_Get_Outlet_Badge_Label extends WP_UnitTestCase {
	public function test_returns_string_when_option_is_string(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_LABEL_OPTION );
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Clearance' );
		register_outlet_badge_label_setting();

		// Act.
		$result = get_outlet_badge_label();

		// Assert.
		$this->assertSame( 'Clearance', $result );
	}

	public function test_returns_empty_string_when_option_is_empty_string(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_LABEL_OPTION );
		update_option( OUTLET_BADGE_LABEL_OPTION, '' );
		register_outlet_badge_label_setting();

		// Act.
		$result = get_outlet_badge_label();

		// Assert.
		$this->assertSame( '', $result );
	}

	public function test_returns_numeric_string_unchanged(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_LABEL_OPTION );
		update_option( OUTLET_BADGE_LABEL_OPTION, '42' );
		register_outlet_badge_label_setting();

		// Act.
		$result = get_outlet_badge_label();

		// Assert.
		$this->assertSame( '42', $result );
	}

	public function test_returns_null_when_option_is_not_set(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_LABEL_OPTION );
		delete_option( OUTLET_BADGE_LABEL_OPTION );
		register_outlet_badge_label_setting();

		// Act.
		$result = get_outlet_badge_label();

		// Assert.
		$this->assertNull( $result );
	}

	public function test_throws_when_option_is_int(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_LABEL_OPTION );
		update_option( OUTLET_BADGE_LABEL_OPTION, 42 );
		register_outlet_badge_label_setting();

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_badge_label();
	}

	public function test_throws_when_option_is_float(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_LABEL_OPTION );
		update_option( OUTLET_BADGE_LABEL_OPTION, 4.2 );
		register_outlet_badge_label_setting();

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_badge_label();
	}

	public function test_throws_when_option_is_bool(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_LABEL_OPTION );
		update_option( OUTLET_BADGE_LABEL_OPTION, true );
		register_outlet_badge_label_setting();

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_badge_label();
	}

	public function test_throws_when_option_is_array(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_LABEL_OPTION );
		update_option( OUTLET_BADGE_LABEL_OPTION, array( 'invalid' ) );
		register_outlet_badge_label_setting();

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_badge_label();
	}

	public function test_throws_when_option_is_object(): void {
		// Arrange.
		unregister_setting( 'outletpro', OUTLET_BADGE_LABEL_OPTION );
		update_option( OUTLET_BADGE_LABEL_OPTION, new stdClass() );
		register_outlet_badge_label_setting();

		// Expect.
		$this->expectException( \UnexpectedValueException::class );

		// Act.
		get_outlet_badge_label();
	}
}
