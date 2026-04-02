<?php
/**
 * Tests for the clearance badge block hooks configuration.
 *
 * @package WC_Clearance
 */

use function WC_Clearance\register_clearance_badge_block;

class Test_Hooked_Clearance_Badge extends WP_UnitTestCase {

	public function test_clearance_badge_is_hooked_after_product_price(): void {
		// Arrange.
		unregister_block_type( 'wc-clearance/clearance-badge' );
		register_clearance_badge_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-badge' );

		// Assert.
		$this->assertArrayHasKey( 'woocommerce/product-price', $block_type->block_hooks );
		$this->assertSame( 'after', $block_type->block_hooks['woocommerce/product-price'] );
	}
}
