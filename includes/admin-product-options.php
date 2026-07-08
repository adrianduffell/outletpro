<?php
/**
 * Admin product options.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize product options.
 *
 * @internal
 */
function init_admin_product_options(): void {
	add_action( 'woocommerce_product_options_inventory_product_data', 'OutletPro\add_product_checkbox_hook' );
	add_action( 'woocommerce_admin_process_product_object', 'OutletPro\save_product_checkbox_hook' );
}

/**
 * Add outlet checkbox to product inventory panel.
 *
 * Fired by `woocommerce_product_options_inventory_product_data`.
 *
 * @internal WordPress action hook
 */
function add_product_checkbox_hook(): void {
	global $post;

	try {
		$is_outlet = $post ? is_outlet( wc_get_product( $post->ID ) ) : false;
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error( 'Could not add outlet checkbox: ' . $e->getMessage() );
		return;
	}

	echo '<div class="outletpro-status-panel">';
	woocommerce_wp_checkbox(
		array(
			'id'          => 'outletpro-status',
			'label'       => __( 'Outlet', 'outletpro' ),
			'description' => __( 'Include in outlet', 'outletpro' ),
			'value'       => $is_outlet ? 'outlet' : '',
			'cbvalue'     => 'outlet',
		)
	);

	// Custom help text under the field.
	$settings_url = admin_url( 'admin.php?page=' . LICENSE_PAGE_SLUG );

	$link = settings_screen_enabled()
		? sprintf(
			' <a href="%s" class="outletpro-button-link">%s</a>',
			esc_url( $settings_url ),
			esc_html__( 'Edit settings', 'outletpro' )
		)
		: '';

	printf(
		'<div class="outletpro-status-help">%s%s</div><!-- .outletpro-status-help -->',
		esc_html__( 'Sell remaining stock in the store’s outlet. Included products display a badge and message.', 'outletpro' ),
		wp_kses_post( $link )
	);
	echo '</div><!-- .outletpro-status-panel -->';
}

/**
 * Save outlet checkbox value.
 *
 * Fired by `woocommerce_admin_process_product_object`.
 *
 * @param \WC_Product $product The product being saved.
 * @internal WordPress action hook
 */
function save_product_checkbox_hook( \WC_Product $product ): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$is_outlet = isset( $_POST['outletpro-status'] );
	try {
		set_outlet( $product, $is_outlet );
	} catch ( \Throwable $e ) {
		$product_id = $product instanceof \WC_Product ? $product->get_id() : null;
		\wc_get_logger()->error(
			'Could not save outlet status for product ID ' . $product_id .
			' with desired status ' . ( $is_outlet ? 'true' : 'false' ) . ': ' . $e->getMessage(),
			array(
				'product_id'     => $product_id,
				'desired_outlet' => $is_outlet,
			)
		);
	}
}
