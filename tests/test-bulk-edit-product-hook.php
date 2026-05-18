<?php
/**
 * Tests for bulk_edit_field_hook() and save_bulk_edit_hook().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\bulk_edit_field_hook;
use function WC_Outlet\register_outlet_status_taxonomy;
use function WC_Outlet\save_bulk_edit_hook;
use function WC_Outlet\seed_outlet_status_taxonomy;
use const WC_Outlet\OUTLET_STATUS_CANONICAL_TERM;
use const WC_Outlet\OUTLET_STATUS_TAXONOMY;

class Test_Bulk_Edit_Product_Hook extends WP_UnitTestCase {

	public function test_bulk_edit_field_hook_renders_select(): void {
		// Expect.
		$this->expectOutputRegex( '/name="wc_outlet_bulk"/' );

		// Act.
		bulk_edit_field_hook();
	}

	public function test_bulk_edit_field_hook_renders_no_change_option(): void {
		// Expect.
		$this->expectOutputRegex( '/<option value="">/' );

		// Act.
		bulk_edit_field_hook();
	}

	public function test_bulk_edit_field_hook_renders_include_option(): void {
		// Expect.
		$this->expectOutputRegex( '/<option value="yes">/' );

		// Act.
		bulk_edit_field_hook();
	}

	public function test_bulk_edit_field_hook_renders_remove_option(): void {
		// Expect.
		$this->expectOutputRegex( '/<option value="no">/' );

		// Act.
		bulk_edit_field_hook();
	}

	public function test_save_bulk_edit_hook_adds_product_to_clearance(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product                = WC_Helper_Product::create_simple_product();
		$_GET['wc_outlet_bulk'] = 'yes';

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( OUTLET_STATUS_CANONICAL_TERM, $terms );

		// Cleanup.
		unset( $_GET['wc_outlet_bulk'] );
	}

	public function test_save_bulk_edit_hook_removes_product_from_clearance(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
		$_GET['wc_outlet_bulk'] = 'no';

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY );
		$this->assertEmpty( $terms );

		// Cleanup.
		unset( $_GET['wc_outlet_bulk'] );
	}

	public function test_save_bulk_edit_hook_does_nothing_when_value_is_empty(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
		$_GET['wc_outlet_bulk'] = '';

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( OUTLET_STATUS_CANONICAL_TERM, $terms );

		// Cleanup.
		unset( $_GET['wc_outlet_bulk'] );
	}

	public function test_save_bulk_edit_hook_does_nothing_when_field_not_set(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
		unset( $_GET['wc_outlet_bulk'] );

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( OUTLET_STATUS_CANONICAL_TERM, $terms );
	}
}
