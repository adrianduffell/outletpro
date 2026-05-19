<?php
/**
 * Tests for display_order_item_outlet_badge_hook().
 *
 * @package WC_Outlet
 */

use function WC_Outlet\display_order_item_outlet_badge_hook;
use function WC_Outlet\hide_order_item_outlet_meta_hook;
use function WC_Outlet\init_admin_order;
use const WC_Outlet\ORDER_ITEM_OUTLET_META_KEY;
use const WC_Outlet\OUTLET_BADGE_LABEL_OPTION;

class Test_Display_Order_Item_Outlet_Badge_Hook extends WP_UnitTestCase {

	public function test_displays_badge_label_for_outlet_order_item(): void {
		// Arrange.
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Last Chance' );
		$item = new WC_Order_Item_Product();
		$item->add_meta_data( ORDER_ITEM_OUTLET_META_KEY, 'yes', true );

		// Expect.
		$this->expectOutputString( '<span class="wc-outlet-admin-badge">Last Chance</span>' );

		// Act.
		display_order_item_outlet_badge_hook( 1, $item, null );
	}

	public function test_displays_default_label_when_no_option_stored(): void {
		// Arrange.
		delete_option( OUTLET_BADGE_LABEL_OPTION );
		$item = new WC_Order_Item_Product();
		$item->add_meta_data( ORDER_ITEM_OUTLET_META_KEY, 'yes', true );

		// Expect.
		$this->expectOutputString( '<span class="wc-outlet-admin-badge">Last chance</span>' );

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
	}

	public function test_hidden_meta_filter_hooked_via_init(): void {
		// Arrange.
		init_admin_order();

		// Assert.
		$this->assertSame( 10, has_filter( 'woocommerce_hidden_order_itemmeta', 'WC_Outlet\hide_order_item_outlet_meta_hook' ) );
	}
}
