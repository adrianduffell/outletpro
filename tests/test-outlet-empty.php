<?php
/**
 * Test the outlet_empty function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\outlet_empty;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Outlet_Empty extends WP_UnitTestCase {

	public function test_throws_exception_when_taxonomy_not_registered(): void {
		// Arrange.
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		outlet_empty();
	}

	public function test_returns_true_when_canonical_term_does_not_exist(): void {
		// Arrange.
		register_outlet_status_taxonomy();

		// Act.
		$result = outlet_empty();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_true_when_no_products_in_outlet(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		// Act.
		$result = outlet_empty();

		// Assert.
		$this->assertTrue( $result );
	}

	public function test_returns_false_when_products_in_outlet(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		$result = outlet_empty();

		// Assert.
		$this->assertFalse( $result );
	}

	public function test_ignores_draft_products(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		$draft_product = \WC_Helper_Product::create_simple_product();
		wp_update_post(
			array(
				'ID'          => $draft_product->get_id(),
				'post_status' => 'draft',
			)
		);
		add_to_outlet( $draft_product );

		// Act.
		$result = outlet_empty();

		// Assert.
		$this->assertTrue( $result );
	}
}
