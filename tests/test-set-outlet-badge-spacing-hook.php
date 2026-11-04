<?php
/**
 * Tests for set_outlet_badge_spacing_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use const OutletPro\SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD;

class Test_Set_Outlet_Badge_Spacing_Hook extends WP_UnitTestCase {

	public function test_badge_above_title_has_bottom_margin(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array( 'core/post-title', 'before' ) );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_block_hooks_to_content( '<!-- wp:post-title /-->', $template );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertSame( 'var:preset|spacing|10', $blocks[0]['attrs']['style']['spacing']['margin']['bottom'] );
	}

	public function test_badge_after_title_has_no_added_margin(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array( 'core/post-title', 'after' ) );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_block_hooks_to_content( '<!-- wp:post-title /-->', $template );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertSame( 'outletpro/outlet-badge', $blocks[1]['blockName'] );
		$this->assertArrayNotHasKey( 'style', $blocks[1]['attrs'] );
	}

	public function test_badge_at_other_anchor_has_no_added_margin(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-price', 'before' ) );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_block_hooks_to_content( '<!-- wp:woocommerce/product-price /-->', $template );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertSame( 'outletpro/outlet-badge', $blocks[0]['blockName'] );
		$this->assertArrayNotHasKey( 'style', $blocks[0]['attrs'] );
	}

	public function test_suppressed_badge_remains_suppressed(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array( 'core/post-title', 'before' ) );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';
		add_filter(
			'hooked_block_outletpro/outlet-badge', // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Dynamic hook includes the block namespace.
			static fn() => null,
			9
		);

		// Act.
		$result = apply_block_hooks_to_content( '<!-- wp:post-title /-->', $template );

		// Assert.
		$this->assertFalse( has_block( 'outletpro/outlet-badge', $result ) );
	}
}
