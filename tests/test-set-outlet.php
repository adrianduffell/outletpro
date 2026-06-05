<?php
/**
 * Test the set_outlet function.
 *
 * @package OutletPro
 */

use function OutletPro\add_to_outlet;
use function OutletPro\is_outlet;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use function OutletPro\set_outlet;
use const OutletPro\OUTLET_STATUS_TAXONOMY;

class Test_Set_Outlet extends \WP_UnitTestCase {
	public function test_adds_to_outlet_when_true(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();

		// Act.
		set_outlet( $product, true );

		// Assert.
		$this->assertTrue( is_outlet( $product ) );
	}

	public function test_removes_from_outlet_when_false(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		// Act.
		set_outlet( $product, false );

		// Assert.
		$this->assertFalse( is_outlet( $product ) );
	}

	public function test_throws_when_taxonomy_missing(): void {
		// Arrange.
		unregister_taxonomy( OUTLET_STATUS_TAXONOMY );
		$product = \WC_Helper_Product::create_simple_product();

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		set_outlet( $product, true );
	}

	public function test_noop_when_already_true(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );

		$before = did_action( 'outlet_pro_status_changed' );

		// Act.
		set_outlet( $product, true );

		// Assert.
		$after = did_action( 'outlet_pro_status_changed' );
		$this->assertSame( $before, $after );
		$this->assertTrue( is_outlet( $product ) );
	}

	public function test_noop_when_already_false(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();

		$before = did_action( 'outlet_pro_status_changed' );

		// Act.
		set_outlet( $product, false );

		// Assert.
		$after = did_action( 'outlet_pro_status_changed' );
		$this->assertSame( $before, $after );
		$this->assertFalse( is_outlet( $product ) );
	}

	public function test_action_passes_correct_values(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();

		$product = \WC_Helper_Product::create_simple_product();

		$action_product_id = null;
		$action_old_value  = null;
		$action_new_value  = null;

		add_action(
			'outlet_pro_status_changed',
			function ( $product_id, $old_outlet, $new_outlet ) use ( &$action_product_id, &$action_old_value, &$action_new_value ): void {
				$action_product_id = $product_id;
				$action_old_value  = $old_outlet;
				$action_new_value  = $new_outlet;
			},
			10,
			3
		);

		// Act.
		set_outlet( $product, true );

		// Assert.
		$this->assertSame( $product->get_id(), $action_product_id );
		$this->assertTrue( $action_new_value );
		$this->assertFalse( $action_old_value );
	}
}
