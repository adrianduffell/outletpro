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
use const OutletPro\SINGLE_PRODUCT_MESSAGE_SHOW_OUTLET_LINK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD;

class Test_Seed_Theme_Mods extends WP_UnitTestCase {

	public function test_seeds_block_theme_mods(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );
		$template_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'single-product',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:woocommerce/product-meta /-->',
			)
		);
		wp_set_object_terms( $template_id, get_stylesheet(), 'wp_theme' );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'core/post-title', 'before' ), get_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce/product-meta', 'first_child' ), get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD ) );
	}

	public function test_seeds_product_details_when_product_meta_is_missing(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD );
		$template_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'single-product',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:woocommerce/product-details /-->',
			)
		);
		wp_set_object_terms( $template_id, get_stylesheet(), 'wp_theme' );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'woocommerce/product-details', 'after' ), get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD ) );
	}

	public function test_seeds_classic_theme_mods(): void {
		// Arrange.
		switch_theme( 'storefront' );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_OUTLET_LINK_THEME_MOD );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce_single_product_summary', 1 ), get_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce_single_product_summary', 1 ), get_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_OUTLET_LINK_THEME_MOD, true ) );
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
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_OUTLET_LINK_THEME_MOD, true );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array(), get_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce_after_add_to_cart_form', 17 ), get_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertTrue( get_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_OUTLET_LINK_THEME_MOD ) );
	}
}
