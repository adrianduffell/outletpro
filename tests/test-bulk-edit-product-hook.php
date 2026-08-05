<?php
/**
 * Tests for bulk_edit_field_hook() and save_bulk_edit_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\bulk_edit_field_hook;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\save_bulk_edit_hook;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_STATUS_CANONICAL_TERM;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Bulk_Edit_Product_Hook extends WP_UnitTestCase {

	public function test_bulk_edit_field_hook_renders_select(): void {
		// Expect.
		$this->expectOutputRegex( '/name="outletpro_bulk"/' );

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

	public function test_save_bulk_edit_hook_adds_product_to_outlet(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product                = WC_Helper_Product::create_simple_product();
		$_GET['outletpro_bulk'] = 'yes';

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( OUTLET_STATUS_CANONICAL_TERM, $terms );

		// Cleanup.
		unset( $_GET['outletpro_bulk'] );
	}

	public function test_save_bulk_edit_hook_removes_product_from_outlet(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
		$_GET['outletpro_bulk'] = 'no';

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY );
		$this->assertEmpty( $terms );

		// Cleanup.
		unset( $_GET['outletpro_bulk'] );
	}

	public function test_save_bulk_edit_hook_does_nothing_when_value_is_empty(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
		$_GET['outletpro_bulk'] = '';

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( OUTLET_STATUS_CANONICAL_TERM, $terms );

		// Cleanup.
		unset( $_GET['outletpro_bulk'] );
	}

	public function test_save_bulk_edit_hook_does_nothing_when_field_not_set(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), OUTLET_STATUS_CANONICAL_TERM, OUTLET_STATUS_TAXONOMY );
		unset( $_GET['outletpro_bulk'] );

		// Act.
		save_bulk_edit_hook( $product );

		// Assert.
		$terms = wp_get_object_terms( $product->get_id(), OUTLET_STATUS_TAXONOMY, array( 'fields' => 'names' ) );
		$this->assertContains( OUTLET_STATUS_CANONICAL_TERM, $terms );
	}
}
