<?php
/**
 * Tests for rest_product_collection_params_hook function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\rest_product_collection_params_hook;

class Test_Rest_Product_Collection_Params_Hook extends WP_UnitTestCase {

	public function test_adds_wc_clearance_status_param(): void {
		// Arrange.
		$params = array();

		// Act.
		$result = rest_product_collection_params_hook( $params );

		// Assert.
		$this->assertArrayHasKey( 'wc_clearance_status', $result );
	}

	public function test_wc_clearance_status_param_has_correct_type(): void {
		// Arrange.
		$params = array();

		// Act.
		$result = rest_product_collection_params_hook( $params );

		// Assert.
		$this->assertSame( 'string', $result['wc_clearance_status']['type'] );
	}

	public function test_wc_clearance_status_param_has_no_default(): void {
		// Arrange.
		$params = array();

		// Act.
		$result = rest_product_collection_params_hook( $params );

		// Assert.
		$this->assertArrayNotHasKey( 'default', $result['wc_clearance_status'] );
	}

	public function test_preserves_existing_params(): void {
		// Arrange.
		$params = array( 'per_page' => array( 'type' => 'integer' ) );

		// Act.
		$result = rest_product_collection_params_hook( $params );

		// Assert.
		$this->assertArrayHasKey( 'per_page', $result );
		$this->assertArrayHasKey( 'wc_clearance_status', $result );
	}
}
