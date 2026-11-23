<?php
/**
 * Tests for after_switch_theme_hook().
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

use function OutletPro\deinit_theme_mods;
use function OutletPro\init_theme_mods;
use const OutletPro\OUTLET_HERO_PATTERN_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD;
use const OutletPro\SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD;

class Test_After_Switch_Theme_Hook extends WP_UnitTestCase {

	public function test_seeds_theme_mods_when_switching_to_purple(): void {
		// Arrange.
		deinit_theme_mods();
		init_theme_mods();
		switch_theme( 'storefront' );
		delete_option( 'theme_mods_purple' );
		// Stub WooCommerce's background thumbnail-regeneration request.
		$regeneration_url = add_query_arg( 'action', 'wp_' . get_current_blog_id() . '_wc_regenerate_images', admin_url( 'admin-ajax.php' ) );
		add_filter(
			'pre_http_request',
			static fn( $preempt, $args, $url ) => 'POST' === ( $args['method'] ?? null ) && 0 === strpos( $url, $regeneration_url . '&' )
				? array( 'response' => array( 'code' => 200 ) )
				: $preempt,
			10,
			3
		);

		// Act.
		switch_theme( 'purple' );
		check_theme_switched();

		// Assert.
		$this->assertSame( 'purple', get_stylesheet() );
		$this->assertSame( 'outletpro/outlet-navigation-price-tiers', get_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD ) );
		$this->assertSame( array( 'core/post-title', 'before' ), get_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertSame( array( 'woocommerce/product-details', 'after' ), get_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD ) );
		$this->assertFalse( get_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD ) );
	}

	public function test_preserves_the_outlet_hero_pattern_when_switching_back(): void {
		// Arrange.
		deinit_theme_mods();
		init_theme_mods();
		switch_theme( 'twentytwentyfive' );
		set_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD, 'outletpro/outlet-navigation-6' );
		switch_theme( 'storefront' );
		self::factory()->term->create_many( 4, array( 'taxonomy' => 'product_cat' ) );
		$regeneration_url = add_query_arg( 'action', 'wp_' . get_current_blog_id() . '_wc_regenerate_images', admin_url( 'admin-ajax.php' ) );
		add_filter(
			'pre_http_request',
			static fn( $preempt, $args, $url ) => 'POST' === ( $args['method'] ?? null ) && 0 === strpos( $url, $regeneration_url . '&' )
				? array( 'response' => array( 'code' => 200 ) )
				: $preempt,
			10,
			3
		);

		// Act.
		switch_theme( 'twentytwentyfive' );
		check_theme_switched();

		// Assert.
		$this->assertSame( 'outletpro/outlet-navigation-6', get_theme_mod( OUTLET_HERO_PATTERN_THEME_MOD ) );
	}
}
