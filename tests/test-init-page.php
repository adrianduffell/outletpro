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
		if ( ! function_exists( 'register_block_template' ) || ! function_exists( 'get_block_template' ) ) {
			// Act.
			init_page();

			// Assert.
			$this->assertTrue( true );
			return;
		}

		// Act.
		init_page();

		// Assert.
		$template = get_block_template( 'wc-outlet//outlet-page', 'wp_template' );
		$this->assertInstanceOf( WP_Block_Template::class, $template );
		$this->assertSame( 'wc-outlet//outlet-page', $template->id );
	}
}
