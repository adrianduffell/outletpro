<?php
/**
 * Tests for seed_outlet_hero_widgets().
 *
 * @package OutletPro
 */

use function OutletPro\init_patterns;
use function OutletPro\seed_outlet_hero_widgets;
use const OutletPro\ACTIVATED_AT_OPTION;
use const OutletPro\OUTLET_HERO_WIDGET_AREA;
use const OutletPro\OUTLET_PAGE_OPTION;

class Test_Seed_Outlet_Hero_Widgets extends WP_UnitTestCase {

	public function test_seeds_detached_navigation_after_previous_activation_without_replacing_other_widgets(): void {
		// Arrange.
		switch_theme( 'storefront' );
		init_patterns();
		delete_option( OUTLET_PAGE_OPTION );
		update_option( ACTIVATED_AT_OPTION, time() );
		update_option( 'widget_block', array( 2 => array( 'content' => 'Other widget' ) ) );
		$GLOBALS['wp_registered_widgets'] = array();
		wp_widgets_init();
		wp_set_sidebars_widgets(
			array(
				'sidebar-other'         => array( 'block-2' ),
				OUTLET_HERO_WIDGET_AREA => array(),
			)
		);

		// Act.
		seed_outlet_hero_widgets();

		// Assert.
		$this->assertCount( 1, wp_get_sidebars_widgets()[ OUTLET_HERO_WIDGET_AREA ] );
		$this->assertSame( array( 'block-2' ), wp_get_sidebars_widgets()['sidebar-other'] );
		$this->assertSame( array( 'content' => 'Other widget' ), get_option( 'widget_block' )[2] );
		$content = implode( '', array_column( get_option( 'widget_block' ), 'content' ) );
		$this->assertStringContainsString( 'wp:outletpro/outlet-tiles', $content );
		$this->assertStringContainsString( 'wp:outletpro/outlet-menu', $content );
		$this->assertStringNotContainsString( '"patternName"', $content );
	}

	public function test_preserves_existing_hero_widgets(): void {
		// Arrange.
		$settings = array( 2 => array( 'content' => 'Custom hero' ) );
		update_option( 'widget_block', $settings );
		wp_set_sidebars_widgets( array( OUTLET_HERO_WIDGET_AREA => array( 'block-2' ) ) );

		// Act.
		seed_outlet_hero_widgets();

		// Assert.
		$this->assertSame( array( 'block-2' ), wp_get_sidebars_widgets()[ OUTLET_HERO_WIDGET_AREA ] );
		$this->assertSame( $settings, get_option( 'widget_block' ) );
	}

	public function test_missing_block_widget_throws(): void { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded -- Restore the native widget after the expected exception.
		// Arrange.
		wp_set_sidebars_widgets( array( OUTLET_HERO_WIDGET_AREA => array() ) );
		unregister_widget( WP_Widget_Block::class );

		// Expect.
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Block widget could not be retrieved.' );

		// Act.
		try {
			seed_outlet_hero_widgets();
		} finally {
			register_widget( WP_Widget_Block::class );
		}
	}
}
