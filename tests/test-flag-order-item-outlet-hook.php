<?php
/**
 * Tests for flag_order_item_outlet_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\flag_order_item_outlet_hook;
use function OutletPro\register_outlet_status_taxonomy;
use function OutletPro\seed_outlet_status_taxonomy;
use const OutletPro\ORDER_ITEM_OUTLET_BADGE_LABEL_META_KEY;
use const OutletPro\ORDER_ITEM_OUTLET_META_KEY;
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;

class Test_Flag_Order_Item_Outlet_Hook extends WP_UnitTestCase {

	public function test_adds_outlet_meta_for_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Final Sale' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$order = wc_create_order();
		$item  = new WC_Order_Item_Product();
		$item->set_product_id( $product->get_id() );
		$item->set_order_id( $order->get_id() );
		$item->save();
		$item_id = $item->get_id();

		// Act.
		flag_order_item_outlet_hook( $item_id, $item, $order->get_id() );

		// Assert.
		$this->assertSame( 'yes', wc_get_order_item_meta( $item_id, ORDER_ITEM_OUTLET_META_KEY, true ) );
		$this->assertSame( 'Final Sale', wc_get_order_item_meta( $item_id, ORDER_ITEM_OUTLET_BADGE_LABEL_META_KEY, true ) );
	}

	public function test_does_not_add_outlet_meta_for_non_outlet_product(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$product = WC_Helper_Product::create_simple_product();
		$order   = wc_create_order();
		$item    = new WC_Order_Item_Product();
		$item->set_product_id( $product->get_id() );
		$item->set_order_id( $order->get_id() );
		$item->save();
		$item_id = $item->get_id();

		// Act.
		flag_order_item_outlet_hook( $item_id, $item, $order->get_id() );

		// Assert.
		$this->assertSame( '', wc_get_order_item_meta( $item_id, ORDER_ITEM_OUTLET_META_KEY, true ) );
		$this->assertSame( '', wc_get_order_item_meta( $item_id, ORDER_ITEM_OUTLET_BADGE_LABEL_META_KEY, true ) );
	}

	public function test_uses_parent_id_for_variation(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$variable_product = WC_Helper_Product::create_variation_product();
		add_to_outlet( $variable_product );
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
		flag_order_item_outlet_hook( $item_id, $item, $order->get_id() );

		// Assert.
		$this->assertSame( 'yes', wc_get_order_item_meta( $item_id, ORDER_ITEM_OUTLET_META_KEY, true ) );
	}

	public function test_does_not_add_badge_label_meta_when_option_is_missing(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		delete_option( OUTLET_BADGE_LABEL_OPTION );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$order = wc_create_order();
		$item  = new WC_Order_Item_Product();
		$item->set_product_id( $product->get_id() );
		$item->set_order_id( $order->get_id() );
		$item->save();
		$item_id = $item->get_id();

		// Act.
		flag_order_item_outlet_hook( $item_id, $item, $order->get_id() );

		// Assert.
		$this->assertSame( 'yes', wc_get_order_item_meta( $item_id, ORDER_ITEM_OUTLET_META_KEY, true ) );
		$this->assertSame( '', wc_get_order_item_meta( $item_id, ORDER_ITEM_OUTLET_BADGE_LABEL_META_KEY, true ) );
	}

	public function test_does_not_add_badge_label_meta_when_option_is_empty_string(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		update_option( OUTLET_BADGE_LABEL_OPTION, '' );
		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		$order = wc_create_order();
		$item  = new WC_Order_Item_Product();
		$item->set_product_id( $product->get_id() );
		$item->set_order_id( $order->get_id() );
		$item->save();
		$item_id = $item->get_id();

		// Act.
		flag_order_item_outlet_hook( $item_id, $item, $order->get_id() );

		// Assert.
		$this->assertSame( 'yes', wc_get_order_item_meta( $item_id, ORDER_ITEM_OUTLET_META_KEY, true ) );
		$this->assertSame( '', wc_get_order_item_meta( $item_id, ORDER_ITEM_OUTLET_BADGE_LABEL_META_KEY, true ) );
	}

	public function test_does_not_add_meta_for_non_product_item(): void {
		// Arrange.
		register_outlet_status_taxonomy();
		seed_outlet_status_taxonomy();
		$item = new WC_Order_Item_Fee();

		// Act.
		flag_order_item_outlet_hook( 0, $item, 0 );

		// Assert.
		$this->assertEmpty( wc_get_order_item_meta( 0, ORDER_ITEM_OUTLET_META_KEY, true ) );
	}
}
