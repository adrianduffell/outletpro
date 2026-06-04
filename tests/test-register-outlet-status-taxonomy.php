<?php
/**
 * Test the register_outlet_status_taxonomy function.
 *
 * @package OutletPro
 */

use function OutletPro\register_outlet_status_taxonomy;

class Test_Register_Outlet_Status_Taxonomy extends \WP_UnitTestCase {

	public function test_registers_taxonomy_successfully(): void {
		// Arrange.
		unregister_taxonomy( 'outletpro_status' );

		// Act.
		register_outlet_status_taxonomy();

		// Assert.
		$this->assertTrue( taxonomy_exists( 'outletpro_status' ) );
		$this->assertContains( 'product', get_taxonomy( 'outletpro_status' )->object_type );
	}

	public function test_calling_function_multiple_times_is_safe(): void {
		// Arrange.

		// Act.
		register_outlet_status_taxonomy();
		register_outlet_status_taxonomy();

		// Assert.
		$this->assertTrue( taxonomy_exists( 'outletpro_status' ) );
	}
}
