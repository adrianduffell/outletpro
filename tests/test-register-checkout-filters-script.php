<?php
/**
 * Tests for register_checkout_filters_script().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_enqueue;
use function OutletPro\register_checkout_filters_script;

class Test_Register_Checkout_Filters_Script extends WP_UnitTestCase {

	public function test_registers_shared_filter_dependencies_without_enqueuing_them(): void {
		// Arrange.
		deinit_enqueue();

		// Act.
		register_checkout_filters_script();

		// Assert.
		$this->assertStringEndsWith( '/assets/js/checkout-filters.js', wp_scripts()->registered['outletpro-checkout-filters']->src );
		$this->assertSame( array( 'wc-blocks-checkout' ), wp_scripts()->registered['outletpro-checkout-filters']->deps );
		$this->assertSame( 1, wp_scripts()->get_data( 'outletpro-checkout-filters', 'group' ) );
		$this->assertFalse( wp_script_is( 'outletpro-checkout-filters', 'enqueued' ) );
	}
}
