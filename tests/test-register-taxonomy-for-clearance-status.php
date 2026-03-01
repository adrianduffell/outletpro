<?php
/**
 * Test the register_taxonomy_for_clearance_status function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_taxonomy_for_clearance_status;

/**
 * Test class for register_taxonomy_for_clearance_status function.
 */
class Test_Register_Taxonomy_For_Clearance_Status extends \WP_UnitTestCase {

	/**
	 * Test that the 'wc_clearance_status' is registered successfully.
	 */
	public function test_registers_taxonomy_successfully(): void {
		// Arrange.
		if ( taxonomy_exists( 'wc_clearance_status' ) ) {
			unregister_taxonomy( 'wc_clearance_status' );
		}

		// Act.
		register_taxonomy_for_clearance_status();

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
		register_taxonomy_for_clearance_status();
		register_taxonomy_for_clearance_status();

		// Assert.
		$this->assertTrue( taxonomy_exists( 'wc_clearance_status' ) );
	}
}
