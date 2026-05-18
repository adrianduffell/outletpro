<?php
/**
 * Test the register_outlet_status_taxonomy function.
 *
 * @package WC_Outlet
 */

use function WC_Outlet\register_outlet_status_taxonomy;

class Test_Register_Outlet_Status_Taxonomy extends \WP_UnitTestCase {

	public function test_registers_taxonomy_successfully(): void {
		// Arrange.
		unregister_taxonomy( 'wc_outlet_status' );

		// Act.
		register_outlet_status_taxonomy();

		// Assert.
		$this->assertTrue( taxonomy_exists( 'wc_outlet_status' ) );
		$this->assertContains( 'product', get_taxonomy( 'wc_outlet_status' )->object_type );
	}

	public function test_calling_function_multiple_times_is_safe(): void {
		// Arrange.

		// Act.
		register_outlet_status_taxonomy();
		register_outlet_status_taxonomy();

		// Assert.
		$this->assertTrue( taxonomy_exists( 'wc_outlet_status' ) );
	}
}
