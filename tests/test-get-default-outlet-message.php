<?php
/**
 * Tests for get_default_outlet_message().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\get_default_outlet_message;

class Test_Get_Default_Outlet_Message extends WP_UnitTestCase {

	public function test_returns_supplies_last_for_us(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'US' );

		// Act.
		$result = get_default_outlet_message();

		// Assert.
		$this->assertSame( 'Only while supplies last', $result );
	}

	public function test_returns_supplies_last_for_us_with_state(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'US:CA' );

		// Act.
		$result = get_default_outlet_message();

		// Assert.
		$this->assertSame( 'Only while supplies last', $result );
	}

	public function test_returns_supplies_last_for_canada(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'CA' );

		// Act.
		$result = get_default_outlet_message();

		// Assert.
		$this->assertSame( 'Only while supplies last', $result );
	}

	public function test_returns_supplies_last_for_canada_with_province(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'CA:ON' );

		// Act.
		$result = get_default_outlet_message();

		// Assert.
		$this->assertSame( 'Only while supplies last', $result );
	}

	public function test_returns_stocks_last_for_uk(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'GB' );

		// Act.
		$result = get_default_outlet_message();

		// Assert.
		$this->assertSame( 'Only while stocks last', $result );
	}

	public function test_returns_stocks_last_for_australia(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'AU' );

		// Act.
		$result = get_default_outlet_message();

		// Assert.
		$this->assertSame( 'Only while stocks last', $result );
	}

	public function test_returns_stocks_last_when_country_not_set(): void {
		// Arrange.
		delete_option( 'woocommerce_default_country' );

		// Act.
		$result = get_default_outlet_message();

		// Assert.
		$this->assertSame( 'Only while stocks last', $result );
	}
}
