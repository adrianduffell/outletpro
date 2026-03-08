<?php
use function WC_Clearance\register_clearance_status_taxonomy;

class Test_Register_Clearance_Status_Taxonomy extends \WP_UnitTestCase {

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

	public function test_calling_function_multiple_times_is_safe(): void {
		// Arrange.

		// Act.
		register_clearance_status_taxonomy();
		register_clearance_status_taxonomy();

		// Assert.
		$this->assertTrue( taxonomy_exists( 'wc_clearance_status' ) );
	}
}
