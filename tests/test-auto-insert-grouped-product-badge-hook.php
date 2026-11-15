<?php
/**
 * Tests for auto_insert_grouped_product_badge_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;

class Test_Auto_Insert_Grouped_Product_Badge_Hook extends WP_UnitTestCase {

	public function test_badge_is_inserted_after_grouped_child_title(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'grouped-product-add-to-cart-with-options';

		// Act.
		$result = apply_block_hooks_to_content( '<!-- wp:woocommerce/add-to-cart-with-options-grouped-product-item-label /-->', $template );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertCount( 2, $blocks );
		$this->assertSame( 'woocommerce/add-to-cart-with-options-grouped-product-item-label', $blocks[0]['blockName'] );
		$this->assertSame( 'outletpro/outlet-badge', $blocks[1]['blockName'] );
	}

	public function test_grouped_child_badge_respects_editor_removal(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$template       = new WP_Block_Template();
		$template->slug = 'grouped-product-add-to-cart-with-options';
		$content        = '<!-- wp:woocommerce/add-to-cart-with-options-grouped-product-item-label {"metadata":{"ignoredHookedBlocks":["outletpro/outlet-badge"]}} /-->';

		// Act.
		$result = apply_block_hooks_to_content( $content, $template );

		// Assert.
		$this->assertFalse( has_block( 'outletpro/outlet-badge', $result ) );
	}

	public function test_badge_is_not_inserted_before_grouped_child_title(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		$result = apply_filters( 'hooked_block_types', array(), 'before', 'woocommerce/add-to-cart-with-options-grouped-product-item-label', null );

		// Assert.
		$this->assertNotContains( 'outletpro/outlet-badge', $result );
	}

	public function test_other_anchors_preserve_existing_hooked_blocks(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		$result = apply_filters( 'hooked_block_types', array( 'core/paragraph' ), 'before', 'core/heading', null );

		// Assert.
		$this->assertSame( array( 'core/paragraph' ), $result );
	}

	public function test_freeform_content_is_unchanged(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		$content = '<p>Legacy product content</p>';

		// Act.
		$result = apply_block_hooks_to_content( $content );

		// Assert.
		$this->assertSame( $content, $result );
	}

	public function test_badge_is_not_inserted_after_deinit_blocks(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();

		// Act.
		deinit_blocks();
		$result = apply_filters( 'hooked_block_types', array(), 'after', 'woocommerce/add-to-cart-with-options-grouped-product-item-label', null );

		// Assert.
		$this->assertSame( array(), $result );
	}
}
