<?php
/**
 * Tests for set_outlet_message_style_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD;

class Test_Set_Outlet_Message_Style_Hook extends WP_UnitTestCase {

	public function test_applies_style_from_theme_mod(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD, 'custom-style' );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';
		$content        = '<!-- wp:woocommerce/product-details /-->';

		// Act.
		$result = apply_block_hooks_to_content( $content, $template );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertSame( 'outletpro/outlet-message', $blocks[1]['blockName'] );
		$this->assertSame( 'is-style-custom-style', $blocks[1]['attrs']['className'] );
	}

	public function test_applies_no_style_when_theme_mod_is_not_defined(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';
		$content        = '<!-- wp:woocommerce/product-details /-->';

		// Act.
		$result = apply_block_hooks_to_content( $content, $template );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertSame( 'outletpro/outlet-message', $blocks[1]['blockName'] );
		$this->assertArrayNotHasKey( 'className', $blocks[1]['attrs'] );
	}
}
