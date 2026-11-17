<?php
/**
 * Widget functions.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Outlet hero sidebar ID.
 *
 * @internal
 */
const OUTLET_HERO_WIDGET_AREA = 'outlet-hero';

/**
 * Initialize widgets.
 *
 * @internal
 */
function init_widgets(): void {
	add_action( 'widgets_init', 'OutletPro\\widgets_init_hook' );
}

/**
 * De-Initialize widgets.
 *
 * @internal
 */
function deinit_widgets(): void {
	remove_action( 'widgets_init', 'OutletPro\\widgets_init_hook' );
	unregister_sidebar( OUTLET_HERO_WIDGET_AREA );
}

/**
 * Register widget areas for classic themes.
 *
 * Fired by `widgets_init`.
 *
 * @internal WordPress action hook
 */
function widgets_init_hook(): void {
	register_classic_outlet_sidebar();
}

/**
 * Register the outlet hero widget area.
 *
 * @internal
 */
function register_classic_outlet_sidebar(): void {
	if ( is_registered_sidebar( OUTLET_HERO_WIDGET_AREA ) ) {
		return;
	}
	register_sidebar(
		array(
			'id'            => OUTLET_HERO_WIDGET_AREA,
			'name'          => __( 'Outlet hero', 'outletpro' ),
			'description'   => __( 'Content for the hero on the outlet page.', 'outletpro' ),
			'before_widget' => '<div id="%1$s" class="%2$s">',
			'after_widget'  => '</div>',
		)
	);
}

/**
 * Render the outlet hero widget area.
 *
 * Fired by the WooCommerce template hook set in the `OUTLET_HERO_TEMPLATE_HOOK_THEME_MOD` theme mod.
 *
 * @internal WordPress action hook
 */
function render_outlet_hero_widgets_hook(): void {
	if ( true !== get_query_var( 'outletpro' ) ) {
		return;
	}
	dynamic_sidebar( OUTLET_HERO_WIDGET_AREA );
}

/**
 * Seeds the outlet hero widget area.
 *
 * @internal
 * @throws \RuntimeException When WordPress's Block widget or navigation is unavailable.
 */
function seed_outlet_hero_widgets(): void {
	$sidebars = wp_get_sidebars_widgets();
	if ( ! empty( $sidebars[ OUTLET_HERO_WIDGET_AREA ] ) ) {
		return;
	}
	$widget = $GLOBALS['wp_widget_factory']->get_widget_object( 'block' );
	if ( ! $widget instanceof \WP_Widget_Block ) {
		throw new \RuntimeException( 'Block widget could not be retrieved.' );
	}
	$blocks = parse_blocks( get_pattern_content( select_outlet_navigation_pattern() ) );
	// Match WordPress's Detach action while retaining labels and design metadata.
	foreach ( array_keys( $blocks ) as $index ) {
		unset( $blocks[ $index ]['attrs']['metadata']['patternName'] );
	}
	$settings            = $widget->get_settings();
	$number              = max( array_merge( array( 1 ), array_filter( array_keys( $settings ), 'is_int' ) ) ) + 1;
	$settings[ $number ] = array( 'content' => serialize_blocks( $blocks ) );
	$widget->save_settings( $settings );
	wp_assign_widget_to_sidebar( 'block-' . $number, OUTLET_HERO_WIDGET_AREA );
}
