<?php
/**
 * Tests for init_outlet_hero_template_hook().
 *
 * @package OutletPro
 */

use function OutletPro\init_outlet_hero_template_hook;
use function OutletPro\seed_theme_mods;
use const OutletPro\OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD;

class Test_Init_Outlet_Hero_Template_Hook extends WP_UnitTestCase {

	public function test_uses_configured_hook_and_priority(): void {
		// Arrange.
		switch_theme( 'storefront' );
		set_theme_mod( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_before_shop_loop', 17 ) );
		seed_theme_mods();
		remove_action( 'woocommerce_archive_description', 'OutletPro\\render_outlet_hero_widgets_hook', 5 );

		// Act.
		init_outlet_hero_template_hook();

		// Assert.
		$this->assertSame( 17, has_action( 'woocommerce_before_shop_loop', 'OutletPro\\render_outlet_hero_widgets_hook' ) );
		$this->assertFalse( has_action( 'woocommerce_archive_description', 'OutletPro\\render_outlet_hero_widgets_hook' ) );
	}

	public function test_empty_setting_disables_widget_rendering(): void {
		// Arrange.
		set_theme_mod( OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD, array() );
		remove_action( 'woocommerce_archive_description', 'OutletPro\\render_outlet_hero_widgets_hook', 5 );

		// Act.
		init_outlet_hero_template_hook();

		// Assert.
		$this->assertFalse( has_action( 'woocommerce_archive_description', 'OutletPro\\render_outlet_hero_widgets_hook' ) );
	}
}
