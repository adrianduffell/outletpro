<?php
/**
 * Tests for flag_order_item_clearance_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\add_to_clearance;
use function WC_Clearance\flag_order_item_clearance_hook;
use function WC_Clearance\register_clearance_status_taxonomy;
use function WC_Clearance\seed_clearance_status_taxonomy;
use const WC_Clearance\ORDER_ITEM_CLEARANCE_META_KEY;

class Test_Flag_Order_Item_Clearance_Hook extends WP_UnitTestCase {

	public function test_adds_clearance_meta_for_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		add_to_clearance( $product );
		$order = wc_create_order();
		$item  = new WC_Order_Item_Product();
		$item->set_product_id( $product->get_id() );
		$item->set_order_id( $order->get_id() );
		$item->save();
		$item_id = $item->get_id();

		// Act.
		flag_order_item_clearance_hook( $item_id, $item, $order->get_id() );

		// Assert.
		$this->assertSame( 'yes', wc_get_order_item_meta( $item_id, ORDER_ITEM_CLEARANCE_META_KEY, true ) );
	}

	public function test_does_not_add_clearance_meta_for_non_clearance_product(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		$order   = wc_create_order();
		$item    = new WC_Order_Item_Product();
		$item->set_product_id( $product->get_id() );
		$item->set_order_id( $order->get_id() );
		$item->save();
		$item_id = $item->get_id();

		// Act.
		flag_order_item_clearance_hook( $item_id, $item, $order->get_id() );

		// Assert.
		$this->assertSame( '', wc_get_order_item_meta( $item_id, ORDER_ITEM_CLEARANCE_META_KEY, true ) );
	}

	public function test_uses_parent_id_for_variation(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$variable_product = WC_Helper_Product::create_variation_product();
		add_to_clearance( $variable_product );
		$variations = $variable_product->get_children();
		$order      = wc_create_order();
		$item       = new WC_Order_Item_Product();
		// WooCommerce stores the parent ID in product_id for variations.
		$item->set_product_id( $variable_product->get_id() );
		$item->set_variation_id( $variations[0] );
		$item->set_order_id( $order->get_id() );
		$item->save();
		$item_id = $item->get_id();

		// Act.
		flag_order_item_clearance_hook( $item_id, $item, $order->get_id() );

		// Assert.
		$this->assertSame( 'yes', wc_get_order_item_meta( $item_id, ORDER_ITEM_CLEARANCE_META_KEY, true ) );
	}

	public function test_does_not_add_meta_for_non_product_item(): void {
		// Arrange.
		register_clearance_status_taxonomy();
		seed_clearance_status_taxonomy();
		$item = new WC_Order_Item_Fee();

		// Act.
		flag_order_item_clearance_hook( 0, $item, 0 );

		// Assert.
		$this->assertSame( '', wc_get_order_item_meta( 0, ORDER_ITEM_CLEARANCE_META_KEY, true ) );
	}
}
