<?php
/**
 * Tests for add_wc_clearance_store_api_param_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_status_taxonomy;

class Test_Add_Wc_Clearance_Store_Api_Param_Hook extends WP_UnitTestCase {

	public function test_wc_clearance_param_is_added(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$params = array();

		// Act.
		$result = apply_filters( 'woocommerce_store_api_product_collection_params', $params );

		// Assert.
		$this->assertArrayHasKey( 'wc_clearance', $result );
		$this->assertSame( 'boolean', $result['wc_clearance']['type'] );
	}

	public function test_existing_params_are_preserved(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		$params = array( 'per_page' => array( 'type' => 'integer' ) );

		// Act.
		$result = apply_filters( 'woocommerce_store_api_product_collection_params', $params );

		// Assert.
		$this->assertArrayHasKey( 'per_page', $result );
		$this->assertArrayHasKey( 'wc_clearance', $result );
	}
}
