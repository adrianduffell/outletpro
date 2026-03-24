<?php
/**
 * Tests for rest_product_collection_params_hook function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\rest_product_collection_params_hook;

class Test_Rest_Product_Collection_Params_Hook extends WP_UnitTestCase {

	public function test_adds_clearance_status_param(): void {
		// Arrange.
		$params = array();

		// Act.
		$result = rest_product_collection_params_hook( $params );

		// Assert.
		$this->assertArrayHasKey( 'clearance_status', $result );
	}

	public function test_clearance_status_param_has_correct_type(): void {
		// Arrange.
		$params = array();

		// Act.
		$result = rest_product_collection_params_hook( $params );

		// Assert.
		$this->assertSame( 'boolean', $result['clearance_status']['type'] );
	}

	public function test_clearance_status_param_defaults_to_false(): void {
		// Arrange.
		$params = array();

		// Act.
		$result = rest_product_collection_params_hook( $params );

		// Assert.
		$this->assertFalse( $result['clearance_status']['default'] );
	}

	public function test_preserves_existing_params(): void {
		// Arrange.
		$params = array( 'per_page' => array( 'type' => 'integer' ) );

		// Act.
		$result = rest_product_collection_params_hook( $params );

		// Assert.
		$this->assertArrayHasKey( 'per_page', $result );
		$this->assertArrayHasKey( 'clearance_status', $result );
	}
}
