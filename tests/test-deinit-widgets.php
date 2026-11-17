<?php
/**
 * Tests for deinit_widgets().
 *
 * @package OutletPro
 */

use function OutletPro\deinit_widgets;
use function OutletPro\init_widgets;
use const OutletPro\OUTLET_HERO_WIDGET_AREA;

class Test_Deinit_Widgets extends WP_UnitTestCase {

	public function test_removes_widget_hooks_and_unregisters_the_hero_area(): void {
		// Arrange.
		switch_theme( 'storefront' );
		init_widgets();
		wp_widgets_init();

		// Act.
		deinit_widgets();

		// Assert.
		$this->assertFalse( has_action( 'widgets_init', 'OutletPro\\widgets_init_hook' ) );
		$this->assertFalse( is_registered_sidebar( OUTLET_HERO_WIDGET_AREA ) );
	}
}
