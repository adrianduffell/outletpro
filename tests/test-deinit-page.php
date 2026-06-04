<?php
/**
 * Tests for deinit_page().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\deinit_page;
use function WC_Outlet\init_page;

class Test_Deinit_Page extends WP_UnitTestCase {

	public function test_template_is_unregistered_after_deinit_page(): void {
		// Arrange.
		deinit_page();
		init_page();
		$template = get_block_template( 'outletpro//outlet-page', 'wp_template' );
		$this->assertNotNull( $template );

		// Act.
		deinit_page();

		// Assert.
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );
	}

	public function test_safely_handles_template_not_registered(): void {
		// Arrange.
		deinit_page();
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );

		// Act.
		deinit_page();

		// Assert.
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );
	}
}
