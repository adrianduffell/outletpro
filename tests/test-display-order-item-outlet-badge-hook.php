<?php
/**
 * Tests for display_order_item_outlet_badge_hook().
 *
 * @package OutletPro
 */

use function OutletPro\display_order_item_outlet_badge_hook;
use function OutletPro\hide_order_item_outlet_meta_hook;
use function OutletPro\init_admin_order;
use const OutletPro\ORDER_ITEM_OUTLET_BADGE_LABEL_META_KEY;
use const OutletPro\ORDER_ITEM_OUTLET_META_KEY;
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;

class Test_Display_Order_Item_Outlet_Badge_Hook extends WP_UnitTestCase {

	public function test_displays_badge_label_for_outlet_order_item(): void {
		// Arrange.
		$item = new WC_Order_Item_Product();
		$item->add_meta_data( ORDER_ITEM_OUTLET_META_KEY, 'yes', true );
		$item->add_meta_data( ORDER_ITEM_OUTLET_BADGE_LABEL_META_KEY, 'Final Sale', true );

		// Expect.
		$this->expectOutputString( '<span class="outletpro-admin-badge">Final Sale</span>' );

		// Act.
		display_order_item_outlet_badge_hook( 1, $item, null );
	}

	public function test_displays_missing_label_indicator_when_order_label_meta_missing_even_if_option_stored(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Last Chance' );
		$item = new WC_Order_Item_Product();
		$item->add_meta_data( ORDER_ITEM_OUTLET_META_KEY, 'yes', true );

		// Expect.
		$this->expectOutputString( '<span class="outletpro-admin-badge">⚠️ Missing label</span>' );

		// Act.
		display_order_item_outlet_badge_hook( 1, $item, null );
	}

	public function test_displays_missing_label_indicator_when_no_order_label_meta_or_option_stored(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_LABEL_OPTION );
		$item = new WC_Order_Item_Product();
		$item->add_meta_data( ORDER_ITEM_OUTLET_META_KEY, 'yes', true );

		// Expect.
		$this->expectOutputString( '<span class="outletpro-admin-badge">⚠️ Missing label</span>' );

		// Act.
		display_order_item_outlet_badge_hook( 1, $item, null );
	}

	public function test_displays_nothing_for_non_outlet_order_item(): void {
		// Arrange.
		$item = new WC_Order_Item_Product();

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		display_order_item_outlet_badge_hook( 1, $item, null );
	}

	public function test_hides_outlet_meta_key(): void {
		// Arrange.
		$hidden_keys = array( '_other_key' );

		// Act.
		$result = hide_order_item_outlet_meta_hook( $hidden_keys );

		// Assert.
		$this->assertContains( ORDER_ITEM_OUTLET_META_KEY, $result );
		$this->assertContains( ORDER_ITEM_OUTLET_BADGE_LABEL_META_KEY, $result );
	}

	public function test_hidden_meta_filter_hooked_via_init(): void {
		// Arrange.
		init_admin_order();

		// Assert.
		$this->assertSame( 10, has_filter( 'woocommerce_hidden_order_itemmeta', 'OutletPro\hide_order_item_outlet_meta_hook' ) );
	}
}
