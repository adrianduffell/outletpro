<?php
/**
 * Test the init_page function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\init_page;

class Test_Init_Page extends WP_UnitTestCase {
	public function test_registers_get_block_templates_patch_filter(): void {
		// Act.
		init_page();

		// Assert.
		$this->assertSame( 999, has_filter( 'get_block_templates', 'WC_Outlet\patch_wp_62407_get_block_templates_hook' ) );
	}
}
