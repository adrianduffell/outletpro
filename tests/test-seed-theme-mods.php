<?php
/**
 * Tests for seed_theme_mods().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\seed_theme_mods;
use const OutletPro\SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD;

class Test_Seed_Theme_Mods extends WP_UnitTestCase {

	public function test_seeds_block_theme_mods(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'core/post-title', 'before' ), get_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce/product-price', 'after' ), get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD ) );
	}

	public function test_seeds_single_product_message_block_style_when_not_defined(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( 'bold', get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD ) );
	}

	public function test_does_not_seed_single_product_message_block_style_when_already_defined(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD, 'notice' );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( 'notice', get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD ) );
	}

	public function test_seeds_single_product_message_block_style_with_notice_on_purple_theme(): void {
		// Arrange.
		switch_theme( 'purple' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( 'notice', get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD ) );
	}

	public function test_seeds_classic_theme_mods(): void {
		// Arrange.
		switch_theme( 'storefront' );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce_single_product_summary', 1 ), get_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce_single_product_summary', 11 ), get_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD ) );
	}

	public function test_preserves_existing_block_theme_mods(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		set_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array( 'core/group', 'after' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array() );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'core/group', 'after' ), get_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( array(), get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD ) );
	}

	public function test_preserves_existing_classic_theme_mods(): void {
		// Arrange.
		switch_theme( 'storefront' );
		set_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD, array() );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_after_add_to_cart_form', 17 ) );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array(), get_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce_after_add_to_cart_form', 17 ), get_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD ) );
	}
}
