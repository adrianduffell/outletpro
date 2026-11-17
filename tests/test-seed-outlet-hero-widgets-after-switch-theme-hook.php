<?php
/**
 * Tests for seed_outlet_hero_widgets_after_switch_theme_hook().
 *
 * @package OutletPro
 */

use function OutletPro\init_patterns;
use function OutletPro\init_widgets;
use const OutletPro\OUTLET_HERO_WIDGET_AREA;

class Test_Seed_Outlet_Hero_Widgets_After_Switch_Theme_Hook extends WP_UnitTestCase {

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPUnit data provider metadata.
	/**
	 * @testWith ["twentytwentyfive", 1]
	 *           ["storefront", 0]
	 */
	public function test_only_seeds_when_the_previous_theme_was_a_block_theme( string $old_stylesheet, int $expected_count ): void {
		// Arrange.
		switch_theme( $old_stylesheet );
		init_patterns();
		update_option( 'widget_block', array() );
		wp_set_sidebars_widgets( array( OUTLET_HERO_WIDGET_AREA => array() ) );
		$GLOBALS['sidebars_widgets'] = wp_get_sidebars_widgets();
		add_filter( 'pre_http_request', static fn() => array( 'response' => array( 'code' => 200 ) ) );

		// Act.
		switch_theme( 'storefront' );
		init_widgets();
		$GLOBALS['wp_registered_widgets'] = array();
		wp_widgets_init();
		check_theme_switched();

		// Assert.
		$this->assertCount( $expected_count, wp_get_sidebars_widgets()[ OUTLET_HERO_WIDGET_AREA ] );
	}
}
