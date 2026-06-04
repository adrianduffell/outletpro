<?php
/**
 * Test the init_page function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\init_page;
use function WC_Outlet\patch_wp_62407_get_block_templates_hook;

class Test_Init_Page extends WP_UnitTestCase {
	public function test_registers_outlet_page_template_when_template_api_is_available(): void {
		// Arrange.
		WP_Block_Templates_Registry::get_instance()->unregister( 'outletpro//outlet-page' );
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );

		// Act.
		init_page();

		// Assert.
		$template = get_block_template( 'outletpro//outlet-page', 'wp_template' );
		$this->assertMatchesRegularExpression( '/outlet-page/', $template->id ); // The namespace differs in tests (default//outlet-page).
		$this->assertSame( 'outletpro', $template->plugin );
	}

	public function test_registers_get_block_templates_patch_filter(): void {
		// Act.
		init_page();

		// Assert.
		$this->assertSame( 999, has_filter( 'get_block_templates', 'WC_Outlet\patch_wp_62407_get_block_templates_hook' ) );
	}
}
