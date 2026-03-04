<?php
/**
 * Test the register_clearance_status_taxonomy function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_status_taxonomy;

/**
 * Test class for register_clearance_status_taxonomy function.
 */
class Test_Register_Clearance_Status_Taxonomy extends \WP_UnitTestCase {

	/**
	 * Test that the 'wc_clearance_status' is registered successfully.
	 */
	public function test_registers_taxonomy_successfully(): void {
		// Arrange.
		if ( taxonomy_exists( 'wc_clearance_status' ) ) {
			unregister_taxonomy( 'wc_clearance_status' );
		}

		// Act.
		register_clearance_status_taxonomy();

		// Assert.
		$this->assertTrue( taxonomy_exists( 'wc_clearance_status' ) );
		$this->assertContains( 'product', get_taxonomy( 'wc_clearance_status' )->object_type );
	}

	/**
	 * Test that calling the function multiple times doesn't cause errors.
	 */
	public function test_calling_function_multiple_times_is_safe(): void {
		// Arrange.

		// Act.
		register_clearance_status_taxonomy();
		register_clearance_status_taxonomy();

		// Assert.
		$this->assertTrue( taxonomy_exists( 'wc_clearance_status' ) );
	}
}
