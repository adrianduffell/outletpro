<?php
/**
 * Tests for seed_theme_mods().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\seed_theme_mods;
use const OutletPro\OUTLET_HERO_PATTERN_THEME_MOD;
use const OutletPro\OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD;
use const OutletPro\PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD;

class Test_Seed_Theme_Mods extends WP_UnitTestCase {

	public function test_seeds_the_outlet_hero_pattern(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD );
		self::factory()->term->create_many( 4, array( 'taxonomy' => 'product_cat' ) );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-4', get_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD ) );
	}

	public function test_preserves_the_outlet_hero_pattern_after_categories_change(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD );
		self::factory()->term->create_many( 3, array( 'taxonomy' => 'product_cat' ) );
		seed_theme_mods();
		self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-3', get_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD ) );
	}

	public function test_keeps_separate_outlet_hero_patterns_for_each_theme(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD );
		self::factory()->term->create_many( 3, array( 'taxonomy' => 'product_cat' ) );
		seed_theme_mods();
		switch_theme( 'purple' );
		remove_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD );
		self::factory()->term->create( array( 'taxonomy' => 'product_cat' ) );

		// Act.
		seed_theme_mods();
		$purple_pattern = get_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD );
		switch_theme( 'twentytwentyfive' );

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-4', $purple_pattern );
		$this->assertSame( 'outletpro/outlet-navigation-3', get_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD ) );
	}

	public function test_skips_hero_anchor_but_seeds_other_mods_when_catalog_template_is_missing(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Restore the template filter even if seeding fails.
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD );
		add_filter( 'get_block_template', '__return_null' );

		// Act.
		try {
			seed_theme_mods();
		} finally {
			remove_filter( 'get_block_template', '__return_null' );
		}

		// Assert.
		$this->assertArrayNotHasKey( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, get_theme_mods() );
		$this->assertSame( array( 'core/post-title', 'before' ), get_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD ) );
	}

	public function test_seeds_hero_anchor_when_catalog_template_becomes_available(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Restore the template filter even if seeding fails.
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD );
		add_filter( 'get_block_template', '__return_null' );
		try {
			seed_theme_mods();
		} finally {
			remove_filter( 'get_block_template', '__return_null' );
		}

		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:query-title /--><!-- wp:term-description /--><!-- wp:woocommerce/store-notices /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'woocommerce/store-notices', 'after' ), get_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD ) );
	}

	public function test_seeds_block_theme_mods(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'archive-product',
				'post_content' => '<!-- wp:term-description /-->',
			)
		);
		wp_set_object_terms( $id, get_stylesheet(), 'wp_theme' );
		remove_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );
		remove_theme_mod( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'core/term-description', 'after' ), get_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'core/post-title', 'before' ), get_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce/product-price', 'after' ), get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertTrue( get_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD ) );
		$this->assertTrue( get_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD ) );
		$this->assertSame( 'fine', get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD ) );
	}

	public function test_seeds_single_product_message_block_style_when_not_defined(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( 'fine', get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD ) );
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

	public function test_seeds_fine_message_text_and_a_link_on_purple(): void {
		// Arrange.
		switch_theme( 'purple' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( 'fine', get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD ) );
		$this->assertTrue( get_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD ) );
		$this->assertTrue( get_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD ) );
	}

	public function test_preserves_disabled_message_defaults_for_a_new_theme(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, false );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, false );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD, 'notice' );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD ) );
		$this->assertSame( 'notice', get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD ) );
	}

	public function test_seeds_missing_message_flags_for_an_existing_hook(): void {
		// Arrange.
		switch_theme( 'purple' );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-details', 'after' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD, 'notice' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertTrue( get_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD ) );
		$this->assertTrue( get_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD ) );
		$this->assertSame( 'notice', get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD ) );
	}

	public function test_repeated_seeding_preserves_changed_message_defaults(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD );
		seed_theme_mods();
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, false );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, false );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD, 'notice' );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD ) );
		$this->assertSame( 'notice', get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD ) );
	}

	public function test_seeds_classic_theme_mods(): void {
		// Arrange.
		switch_theme( 'storefront' );
		remove_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD );
		remove_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD );
		remove_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD );
		remove_theme_mod( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertFalse( get_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce_single_product_summary', 1 ), get_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce_single_product_summary', 11 ), get_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertTrue( get_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce_archive_description', 5 ), get_theme_mod( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD ) );
	}

	public function test_preserves_existing_block_theme_mods(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array( 'core/query-title', 'before' ) );
		set_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array( 'core/group', 'after' ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array() );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array( 'core/query-title', 'before' ), get_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'core/group', 'after' ), get_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( array(), get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD ) );
	}

	public function test_preserves_disabled_outlet_page_hook(): void {
		// Arrange.
		switch_theme( 'twentytwentyfive' );
		set_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD, array() );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array(), get_theme_mod( PRODUCT_CATALOG_CONDITIONAL_BLOCK_HOOK_THEME_MOD ) );
	}

	public function test_preserves_existing_classic_theme_mods(): void {
		// Arrange.
		switch_theme( 'storefront' );
		set_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD, array() );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_after_add_to_cart_form', 17 ) );
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, false );
		set_theme_mod( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD, array() );

		// Act.
		seed_theme_mods();

		// Assert.
		$this->assertSame( array(), get_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce_after_add_to_cart_form', 17 ), get_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD ) );
		$this->assertSame( array(), get_theme_mod( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD ) );
	}
}
