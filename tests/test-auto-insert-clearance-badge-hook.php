<?php
/**
 * Tests for auto_insert_clearance_badge_hook().
 *
 * @package WC_Clearance
 */

use function WC_Clearance\blocks_init;
use function WC_Clearance\register_clearance_badge_block;
use function WC_Clearance\reset_blocks;

class Test_Auto_Insert_Clearance_Badge_Hook extends WP_UnitTestCase {

	public function test_clearance_badge_has_no_block_hooks_declaration(): void {
		// Arrange.
		reset_blocks();
		register_clearance_badge_block();

		// Act.
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wc-clearance/clearance-badge' );

		// Assert: the PHP filter is the sole source of truth; block.json must not declare blockHooks.
		$this->assertEmpty( $block_type->block_hooks );
	}

	public function test_badge_is_not_added_when_context_is_array(): void {
		// Arrange.
		reset_blocks();
		blocks_init();

		// Act: WordPress can pass an array (e.g. pattern context) — must not cause a fatal.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', array() );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $result );
	}

	public function test_badge_is_not_added_when_context_is_null(): void {
		// Arrange.
		reset_blocks();
		blocks_init();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', null );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $result );
	}

	public function test_badge_is_not_added_when_template_is_not_single_product(): void {
		// Arrange.
		reset_blocks();
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', $template );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $result );
	}

	public function test_badge_is_added_when_template_is_single_product(): void {
		// Arrange.
		reset_blocks();
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/product-price', $template );

		// Assert.
		$this->assertContains( 'wc-clearance/clearance-badge', $result );
	}

	public function test_other_hooked_blocks_are_not_filtered(): void {
		// Arrange.
		reset_blocks();
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'archive-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array( 'core/paragraph' ), 'after', 'woocommerce/product-price', $template );

		// Assert.
		$this->assertContains( 'core/paragraph', $result );
	}

	public function test_badge_is_not_added_for_different_anchor(): void {
		// Arrange.
		reset_blocks();
		blocks_init();
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'core/heading', $template );

		// Assert.
		$this->assertNotContains( 'wc-clearance/clearance-badge', $result );
	}
}
