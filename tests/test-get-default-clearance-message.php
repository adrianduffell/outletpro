<?php
/**
 * Test the get_default_clearance_message function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\get_default_clearance_message;

class Test_Get_Default_Clearance_Message extends WP_UnitTestCase {

	public function test_returns_stocks_last_for_non_us_store(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'GB' );

		// Act.
		$result = get_default_clearance_message();

		// Assert.
		$this->assertSame( 'Only while stocks last', $result );
	}

	public function test_returns_supplies_last_for_us_store(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'US' );

		// Act.
		$result = get_default_clearance_message();

		// Assert.
		$this->assertSame( 'Only while supplies last', $result );
	}

	public function test_returns_supplies_last_for_us_store_with_state(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'US:TX' );

		// Act.
		$result = get_default_clearance_message();

		// Assert.
		$this->assertSame( 'Only while supplies last', $result );
	}

	public function test_returns_supplies_last_for_canadian_store(): void {
		// Arrange.
		update_option( 'woocommerce_default_country', 'CA' );

		// Act.
		$result = get_default_clearance_message();

		// Assert.
		$this->assertSame( 'Only while supplies last', $result );
	}
}
