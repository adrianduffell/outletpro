<?php
/**
 * Test the init_page function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\init_page;

class Test_Init_Page extends WP_UnitTestCase {

	public function test_registers_outlet_page_template_when_template_api_is_available(): void {
		// Arrange.
		WP_Block_Templates_Registry::get_instance()->unregister( 'wc-outlet//outlet-page' );
		$this->assertNull( get_block_template( 'wc-outlet//outlet-page', 'wp_template' ) );

		// Act.
		init_page();

		// Assert.
		$template = get_block_template( 'wc-outlet//outlet-page', 'wp_template' );
		$this->assertMatchesRegularExpression( '/outlet-page/', $template->id ); // The namespace differs in tests (default//outlet-page).
	}
}
