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

class Test_Init_Page extends WP_UnitTestCase {

	public function test_registers_catalog_without_a_standalone_outlet_template(): void {
		// Arrange.
		deinit_page();

		// Act.
		init_page();

		// Assert.
		$this->assertNotFalse( has_filter( 'request', 'OutletPro\\outlet_catalog_request_hook' ) );
		$this->assertNull( get_block_template( 'outletpro//outlet-page', 'wp_template' ) );
	}
}
