<?php
/**
 * Test the add_badge_to_stock_html_hook function.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\add_to_outlet;
use function OutletPro\init_admin_product_list_table;
use const OutletPro\OUTLET_BADGE_LABEL_OPTION;

class Test_Add_Badge_To_Stock_Html_Hook extends WP_UnitTestCase {

	public function test_adds_badge_when_product_is_outlet(): void {
		// Arrange.
		init_admin_product_list_table();

		$product = WC_Helper_Product::create_simple_product();
		add_to_outlet( $product );
		update_option( OUTLET_BADGE_LABEL_OPTION, 'Last chance' );

		// Act.
		$result = apply_filters(
			'woocommerce_admin_stock_html',
			'In stock',
			$product
		);

		// Assert.
		$this->assertSame(
			'In stock<div class="outletpro-admin-badge">Last chance</div>',
			$result
		);
	}

	public function test_does_not_add_badge_when_product_is_not_outlet(): void {
		// Arrange.
		init_admin_product_list_table();

		$product = WC_Helper_Product::create_simple_product();

		// Act.
		$result = apply_filters(
			'woocommerce_admin_stock_html',
			'In stock',
			$product
		);

		// Assert.
		$this->assertSame( 'In stock', $result );
	}
}
