<?php
/**
 * Tests for populate_outlet_message_style_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_blocks;
use function OutletPro\init_blocks;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD;

class Test_Populate_Outlet_Message_Style_Hook extends WP_UnitTestCase {

	public function test_applies_style_without_enabling_strong_text_or_a_link(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD, 'custom-style' );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, false );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, false );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';
		$content        = '<!-- wp:woocommerce/product-details /-->';

		// Act.
		$result = apply_block_hooks_to_content( $content, $template );
		$blocks = parse_blocks( $result );

		// Assert.
		$this->assertSame( 'outletpro/outlet-message', $blocks[1]['blockName'] );
		$this->assertSame( 'is-style-custom-style', $blocks[1]['attrs']['className'] );
		$this->assertFalse( $blocks[1]['attrs']['strong'] );
		$this->assertSame( array(), $blocks[1]['innerBlocks'] );
	}

	public function test_populates_strong_text_and_a_link_without_a_style(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, true );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, true );
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
		$this->assertTrue( $blocks[1]['attrs']['strong'] );
		$this->assertCount( 1, $blocks[1]['innerBlocks'] );
		$this->assertSame( 'outletpro/outlet-link', $blocks[1]['innerBlocks'][0]['blockName'] );
		$this->assertSame( array( null ), $blocks[1]['innerContent'] );
	}

	public function test_missing_flags_preserve_the_existing_message(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$blocks = parse_blocks( apply_block_hooks_to_content( '<!-- wp:woocommerce/product-details /-->', $template ) );

		// Assert.
		$this->assertFalse( $blocks[1]['attrs']['strong'] );
		$this->assertSame( array(), $blocks[1]['innerBlocks'] );
	}

	public function test_preserves_a_suppressed_message(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, true );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, true );
		add_filter( 'hooked_block_outletpro/outlet-message', '__return_null', 9 );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$result = apply_block_hooks_to_content( '<!-- wp:woocommerce/product-details /-->', $template );

		// Assert.
		$this->assertFalse( has_block( 'outletpro/outlet-message', $result ) );
	}

	public function test_preserves_an_existing_link_and_explicit_strong_attribute(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, true );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, true );
		add_filter(
			'hooked_block_outletpro/outlet-message',
			static fn() => parse_blocks( '<!-- wp:outletpro/outlet-message {"strong":false} --><!-- wp:outletpro/outlet-link {"text":"Browse deals"} /--><!-- /wp:outletpro/outlet-message -->' )[0],
			9
		);
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$blocks = parse_blocks( apply_block_hooks_to_content( '<!-- wp:woocommerce/product-details /-->', $template ) );

		// Assert.
		$this->assertFalse( $blocks[1]['attrs']['strong'] );
		$this->assertCount( 1, $blocks[1]['innerBlocks'] );
		$this->assertSame( 'Browse deals', $blocks[1]['innerBlocks'][0]['attrs']['text'] );
	}

	public function test_preserves_saved_message_customizations(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, true );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, true );
		$template          = new WP_Block_Template();
		$template->slug    = 'single-product';
		$template->content = '<!-- wp:woocommerce/product-details {"metadata":{"ignoredHookedBlocks":["outletpro/outlet-message"]}} /--><!-- wp:outletpro/outlet-message {"strong":false,"className":"is-style-notice"} /-->';

		// Act.
		$blocks = parse_blocks( apply_block_hooks_to_content( $template->content, $template ) );

		// Assert.
		$this->assertCount( 2, $blocks );
		$this->assertFalse( $blocks[1]['attrs']['strong'] );
		$this->assertSame( 'is-style-notice', $blocks[1]['attrs']['className'] );
		$this->assertSame( array(), $blocks[1]['innerBlocks'] );
	}

	public function test_treats_invalid_link_visibility_as_false(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, true );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, 'true' );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$blocks = parse_blocks( apply_block_hooks_to_content( '<!-- wp:woocommerce/product-details /-->', $template ) );

		// Assert.
		$this->assertTrue( $blocks[1]['attrs']['strong'] );
		$this->assertSame( array(), $blocks[1]['innerBlocks'] );
	}

	public function test_treats_invalid_message_emphasis_as_false(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, 'true' );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, false );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$blocks = parse_blocks( apply_block_hooks_to_content( '<!-- wp:woocommerce/product-details /-->', $template ) );

		// Assert.
		$this->assertFalse( $blocks[1]['attrs']['strong'] );
	}

	public function test_ignores_an_invalid_style_without_suppressing_the_message(): void {
		// Arrange.
		deinit_blocks();
		init_blocks();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD, false );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, true );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, true );
		$template       = new WP_Block_Template();
		$template->slug = 'single-product';

		// Act.
		$blocks = parse_blocks( apply_block_hooks_to_content( '<!-- wp:woocommerce/product-details /-->', $template ) );

		// Assert.
		$this->assertArrayNotHasKey( 'className', $blocks[1]['attrs'] );
		$this->assertTrue( $blocks[1]['attrs']['strong'] );
		$this->assertSame( 'outletpro/outlet-link', $blocks[1]['innerBlocks'][0]['blockName'] );
	}
}
