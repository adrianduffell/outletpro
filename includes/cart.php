<?php
/**
 * Cart functions.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize cart integrations.
 *
 * @internal
 */
function init_cart(): void {
	/* Block integrations. */

	// Cart block: enqueue cart-block assets.
	add_filter( 'render_block_woocommerce/cart', 'OutletPro\enqueue_block_cart_assets_hook' );

	// Checkout block: enqueue cart-block assets.
	add_filter( 'render_block_woocommerce/checkout', 'OutletPro\enqueue_block_cart_assets_hook' );

	// Mini-Cart: enqueue cart-block assets.
	add_filter( 'render_block_woocommerce/mini-cart', 'OutletPro\enqueue_block_cart_assets_hook' );

	/* Classic integrations. */

	// Add class name to cart item rows.
	add_filter( 'woocommerce_cart_item_class', 'OutletPro\add_outlet_to_cart_item_class_hook', 10, 2 );

	// Cart / Checkout: Display the badge before the product name.
	add_filter( 'woocommerce_cart_item_name', 'OutletPro\display_cart_item_outlet_badge_hook', 10, 2 );
}

/**
 * Helper to de-initialize cart integrations back to the uninitialized state.
 *
 * @internal
 */
function deinit_cart(): void {
	remove_filter( 'render_block_woocommerce/cart', 'OutletPro\enqueue_block_cart_assets_hook' );
	remove_filter( 'render_block_woocommerce/checkout', 'OutletPro\enqueue_block_cart_assets_hook' );
	remove_filter( 'render_block_woocommerce/mini-cart', 'OutletPro\enqueue_block_cart_assets_hook' );
	remove_filter( 'woocommerce_get_item_data', 'OutletPro\add_outlet_to_cart_item_meta_hook', PHP_INT_MAX );
	remove_filter( 'woocommerce_cart_item_class', 'OutletPro\add_outlet_to_cart_item_class_hook' );
	remove_filter( 'woocommerce_cart_item_name', 'OutletPro\display_cart_item_outlet_badge_hook' );
}

/**
 * Enqueue the front-end block cart badge assets.
 *
 * Fired by `render_block_woocommerce/cart`.
 * Fired by `render_block_woocommerce/checkout`.
 * Fired by `render_block_woocommerce/mini-cart`.
 *
 * @param string $block_content The rendered block content.
 * @return string Unmodified block content.
 * @internal WordPress filter hook
 */
function enqueue_block_cart_assets_hook( string $block_content ): string {
	wp_enqueue_style( 'outletpro-block-cart' );
	wp_enqueue_script( 'outletpro-block-cart' );

	return $block_content;
}

/**
 * Adds outlet status into the cart item meta.
 *
 * Fired by `woocommerce_get_item_data`.
 *
 * @param array $item_data The existing cart item data.
 * @param array $cart_item The cart item.
 * @return array<int, array<string, mixed>>
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 */
function add_outlet_to_cart_item_meta_hook( $item_data, $cart_item ): array {
	$product = $cart_item['data'] ?? null;

	if ( ! $product instanceof \WC_Product ) {
		return $item_data;
	}

	try {
		if ( ! is_outlet( $product ) ) {
			return $item_data;
		}
	} catch ( \Throwable $e ) {
		return $item_data;
	}

	$outlet_label = get_option( OUTLET_BADGE_LABEL_OPTION );

	if ( ! is_string( $outlet_label ) || '' === trim( $outlet_label ) ) {
		return $item_data;
	}

	// Important: The CSS badge replacement expects outlet meta to be first.
	array_unshift(
		$item_data,
		array(
			'key'                                      => $outlet_label,
			'value'                                    => __( 'Yes', 'outletpro' ),
			'display'                                  => sprintf(
				'<span class="outletpro-cart-item-meta">%s</span>',
				esc_html__( 'Yes', 'outletpro' )
			),
			'__experimental_woocommerce_blocks_hidden' => true,
		)
	);

	return $item_data;
}

/**
 * Add .outletpro-cart-item class name to rows in the classic cart when the cart item is an outlet product.
 *
 * Fired by `woocommerce_cart_item_class`.
 *
 * @param string $classes The existing cart item classes.
 * @param array  $cart_item The cart item.
 * @return string Filtered cart item classes.
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 */
function add_outlet_to_cart_item_class_hook( $classes, $cart_item ): string {
	$product = $cart_item['data'] ?? null;

	if ( ! $product instanceof \WC_Product ) {
		return $classes;
	}

	try {
		if ( ! is_outlet( $product ) ) {
			return $classes;
		}
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error( 'Outlet status could not be retrieved in classic cart item' );
		return $classes;
	}

	if ( '' === $classes ) {
		return 'outletpro-cart-item';
	}

	return $classes . ' outletpro-cart-item';
}

/**
 * Display the outlet badge before the product name in the classic cart and checkout.
 *
 * Fired by `woocommerce_cart_item_name`.
 *
 * @param string $product_name The product name HTML.
 * @param array  $cart_item    The cart item.
 * @return string Filtered product name HTML.
 * @internal WordPress filter hook
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
 */
function display_cart_item_outlet_badge_hook( $product_name, $cart_item ): string {
	if ( ! is_cart() && ! is_checkout() ) {
		return $product_name;
	}

	$product = $cart_item['data'] ?? null;

	if ( ! $product instanceof \WC_Product ) {
		return $product_name;
	}

	try {
		if ( ! is_outlet( $product ) ) {
			return $product_name;
		}
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error( 'Outlet status could not be retrieved in classic cart item' );
		return $product_name;
	}

	try {
		$label = get_outlet_badge_label();
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error( 'Outlet badge label could not be retrieved in classic cart item' );
		return $product_name;
	}

	wp_enqueue_style( 'outletpro-classic-badge' );

	return sprintf(
		'<div class="outletpro-badge-container"><div class="outletpro-badge">%s</div></div>',
		esc_html( $label )
	) . $product_name;
}
