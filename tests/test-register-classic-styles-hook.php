<?php
/**
 * Tests for register_classic_styles_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_classic_styles_hook;

class Test_Register_Classic_Styles_Hook extends WP_UnitTestCase {

	public function test_style_is_registered_when_hook_fires(): void {
		// Arrange.
		wp_deregister_style( 'wc-clearance' );

		// Act.
		register_classic_styles_hook();

		// Assert.
		$this->assertTrue( wp_style_is( 'wc-clearance', 'registered' ) );
	}

	public function test_style_is_not_enqueued_when_hook_fires(): void {
		// Arrange.
		wp_deregister_style( 'wc-clearance' );

		// Act.
		register_classic_styles_hook();

		// Assert.
		$this->assertFalse( wp_style_is( 'wc-clearance', 'enqueued' ) );
	}
}
