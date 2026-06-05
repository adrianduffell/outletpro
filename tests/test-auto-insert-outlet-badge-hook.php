<?php
/**
 * Tests for auto_insert_outlet_badge_hook().
 *
 * @package OutletPro
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use function OutletPro\register_outlet_badge_block;

class Test_Auto_Insert_Outlet_Badge_Hook extends WP_UnitTestCase {

	public function test_outlet_badge_has_no_block_hooks_declaration(): void {
		// Arrange.
		deinit_blocks();
		register_outlet_badge_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'outletpro/outlet-badge' );

		// Assert.
		$this->assertEmpty( $block_type->block_hooks );
	}

	public function test_badge_is_not_added_when_context_is_array(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', array() );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-badge', $result );
	}

	public function test_badge_is_not_added_when_context_is_null(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', null );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-badge', $result );
	}

	public function test_badge_is_not_added_when_template_is_not_single_product(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', $template );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-badge', $result );
	}

	public function test_badge_is_added_when_template_is_single_product(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', $template );

		// Assert.
		$this->assertContains( 'outletpro/outlet-badge', $result );
	}

	public function test_other_hooked_blocks_are_not_filtered(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array( 'core/paragraph' ), 'after', 'woocommerce/product-price', $template );

		// Assert.
		$this->assertContains( 'core/paragraph', $result );
	}

	public function test_badge_is_not_added_for_different_anchor(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'core/heading', $template );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-badge', $result );
	}
}
