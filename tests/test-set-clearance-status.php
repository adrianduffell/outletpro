<?php
/**
 * Test the set_clearance_status function.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\is_clearance;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use function WC_Clearance\set_clearance_status;
use const WC_Clearance\CLEARANCE_STATUS_TAXONOMY;

class Test_Set_Clearance_Status extends \WP_UnitTestCase {
	public function test_adds_to_clearance_when_true(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();

		// Act.
		set_clearance_status( $product, true );

		// Assert.
		$this->assertTrue( is_clearance( $product ) );
	}

	public function test_removes_from_clearance_when_false(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );

		// Act.
		set_clearance_status( $product, false );

		// Assert.
		$this->assertFalse( is_clearance( $product ) );
	}

	public function test_throws_when_taxonomy_missing(): void {
		// Arrange.
		if ( taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) ) {
			unregister_taxonomy( CLEARANCE_STATUS_TAXONOMY );
		}
		$product = \WC_Helper_Product::create_simple_product();

		// Expect.
		$this->expectException( \RuntimeException::class );

		// Act.
		set_clearance_status( $product, true );
	}

	public function test_noop_when_already_true(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );

		$before = did_action( 'wc_clearance_status_changed' );

		// Act.
		set_clearance_status( $product, true );

		// Assert.
		$after = did_action( 'wc_clearance_status_changed' );
		$this->assertSame( $before, $after );
		$this->assertTrue( is_clearance( $product ) );
	}

	public function test_noop_when_already_false(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = \WC_Helper_Product::create_simple_product();

		$before = did_action( 'wc_clearance_status_changed' );

		// Act.
		set_clearance_status( $product, false );

		// Assert.
		$after = did_action( 'wc_clearance_status_changed' );
		$this->assertSame( $before, $after );
		$this->assertFalse( is_clearance( $product ) );
	}

	public function test_action_passes_correct_values(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();

		$product = \WC_Helper_Product::create_simple_product();

		$action_product_id = null;
		$action_old_value  = null;
		$action_new_value  = null;

		add_action(
			'wc_clearance_status_changed',
			function ( $product_id, $old_clearance, $new_clearance ) use ( &$action_product_id, &$action_old_value, &$action_new_value ): void {
				$action_product_id = $product_id;
				$action_old_value  = $old_clearance;
				$action_new_value  = $new_clearance;
			},
			10,
			3
		);

		// Act.
		set_clearance_status( $product, true );

		// Assert.
		$this->assertSame( $product->get_id(), $action_product_id );
		$this->assertTrue( $action_new_value );
		$this->assertFalse( $action_old_value );
	}
}
