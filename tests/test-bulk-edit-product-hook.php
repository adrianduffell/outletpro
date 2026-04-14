<?php
/**
 * Tests for bulk_edit_field_hook() and save_bulk_edit_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\bulk_edit_field_hook;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\save_bulk_edit_hook;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\CLEARANCE_STATUS_CANONICAL_TERM;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Bulk_Edit_Product_Hook extends WP_UnitTestCase {

	public function test_bulk_edit_field_hook_renders_select(): void {
		// Expect.
		$this->expectOutputRegex( '/name="wc_clearance_bulk"/' );

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
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product                   = WC_Helper_Product::create_simple_product();
		$_GET['wc_clearance_bulk'] = 'yes';

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), CLEARANCE_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( CLEARANCE_STATUS_CANONICAL_TERM, $terms );

		// Cleanup.
		unset( $_GET['wc_clearance_bulk'] );
	}

	public function test_save_bulk_edit_hook_removes_product_from_clearance(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$_GET['wc_clearance_bulk'] = 'no';

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), CLEARANCE_STATUS_TAXONOMY );
		$this->assertEmpty( $terms );

		// Cleanup.
		unset( $_GET['wc_clearance_bulk'] );
	}

	public function test_save_bulk_edit_hook_does_nothing_when_value_is_empty(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		$_GET['wc_clearance_bulk'] = '';

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), CLEARANCE_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( CLEARANCE_STATUS_CANONICAL_TERM, $terms );

		// Cleanup.
		unset( $_GET['wc_clearance_bulk'] );
	}

	public function test_save_bulk_edit_hook_does_nothing_when_field_not_set(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );
		unset( $_GET['wc_clearance_bulk'] );

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), CLEARANCE_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( CLEARANCE_STATUS_CANONICAL_TERM, $terms );
	}
}
