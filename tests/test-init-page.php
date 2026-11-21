<?php
/**
 * Test the init_page function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_page;
use function OutletPro\init_page;
use const OutletPro\LEGACY_OUTLET_PAGE_TEMPLATE_OPTION;

class Test_Init_Page extends WP_UnitTestCase {

	public function test_registers_outlet_page_template_when_legacy_option_is_yes(): void {
		// Arrange.
		deinit_page();
		update_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION, 'yes' );
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );

		// Act.
		init_page();

		// Assert.
		$template = get_block_template( 'outletpro//outlet-page', 'wp_template' );
		$this->assertMatchesRegularExpression( '/outlet-page/', $template->id ); // The namespace differs in tests (default//outlet-page).
		$this->assertSame( 'outletpro', $template->plugin );
	}

	public function test_does_not_register_outlet_page_template_when_legacy_option_is_missing(): void {
		// Arrange.
		deinit_page();
		delete_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION );

		// Act.
		init_page();

		// Assert.
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );
	}

	public function test_does_not_register_outlet_page_template_when_legacy_option_is_no(): void {
		// Arrange.
		deinit_page();
		update_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION, 'no' );

		// Act.
		init_page();

		// Assert.
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );
	}
}
