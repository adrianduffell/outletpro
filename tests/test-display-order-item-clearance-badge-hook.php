<?php
/**
 * Tests for display_order_item_clearance_badge_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\display_order_item_clearance_badge_hook;
use function WC_Clearance\init_admin_order;
use const WC_Clearance\CLEARANCE_BADGE_LABEL_OPTION;
use const WC_Clearance\ORDER_ITEM_CLEARANCE_META_KEY;

class Test_Display_Order_Item_Clearance_Badge_Hook extends WP_UnitTestCase {

	public function test_displays_badge_label_for_clearance_order_item(): void {
		// Arrange.
		update_option( CLEARANCE_BADGE_LABEL_OPTION, 'Last Chance' );
		$item = new WC_Order_Item_Product();
		$item->add_meta_data( ORDER_ITEM_CLEARANCE_META_KEY, '1', true );

		// Expect.
		$this->expectOutputString( 'Last Chance' );

		// Act.
		display_order_item_clearance_badge_hook( 1, $item, null );
	}

	public function test_displays_default_label_when_no_option_stored(): void {
		// Arrange.
		delete_option( CLEARANCE_BADGE_LABEL_OPTION );
		$item = new WC_Order_Item_Product();
		$item->add_meta_data( ORDER_ITEM_CLEARANCE_META_KEY, '1', true );

		// Expect.
		$this->expectOutputString( 'Clearance' );

		// Act.
		display_order_item_clearance_badge_hook( 1, $item, null );
	}

	public function test_displays_nothing_for_non_clearance_order_item(): void {
		// Arrange.
		$item = new WC_Order_Item_Product();

		// Expect.
		$this->expectOutputString( '' );

		// Act.
		display_order_item_clearance_badge_hook( 1, $item, null );
	}

	public function test_hooked_to_woocommerce_after_order_itemmeta(): void {
		// Arrange.
		init_admin_order();

		// Assert.
		$this->assertSame( 10, has_action( 'woocommerce_after_order_itemmeta', 'WC_Clearance\display_order_item_clearance_badge_hook' ) );
	}
}
