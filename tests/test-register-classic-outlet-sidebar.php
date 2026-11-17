<?php
/**
 * Tests for register_classic_outlet_sidebar().
 *
 * @package OutletPro
 */

use function OutletPro\init_widgets;
use function OutletPro\register_classic_outlet_sidebar;
use const OutletPro\OUTLET_HERO_WIDGET_AREA;

class Test_Register_Classic_Outlet_Sidebar extends WP_UnitTestCase {

	public function test_registers_during_native_widget_initialization(): void {
		// Arrange.
		switch_theme( 'storefront' );
		unregister_sidebar( OUTLET_HERO_WIDGET_AREA );
		init_widgets();

		// Act.
		wp_widgets_init();

		// Assert.
		$this->assertTrue( is_registered_sidebar( OUTLET_HERO_WIDGET_AREA ) );
	}

	public function test_repeated_registration_preserves_the_existing_registration(): void {
		// Arrange.
		register_classic_outlet_sidebar();
		$GLOBALS['wp_registered_sidebars'][ OUTLET_HERO_WIDGET_AREA ]['name'] = 'Custom hero';
		$before = $GLOBALS['wp_registered_sidebars'][ OUTLET_HERO_WIDGET_AREA ];

		// Act.
		register_classic_outlet_sidebar();

		// Assert.
		$this->assertSame( $before, $GLOBALS['wp_registered_sidebars'][ OUTLET_HERO_WIDGET_AREA ] );
	}
}
