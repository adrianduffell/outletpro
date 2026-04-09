<?php
/**
 * Admin product options.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize product options.
 *
 * @internal
 */
function init_admin_product_options(): void {
	add_action( 'woocommerce_product_options_general_product_data', 'WC_Clearance\add_product_checkbox_hook' );
	add_action( 'woocommerce_admin_process_product_object', 'WC_Clearance\save_product_checkbox_hook' );
}

/**
 * Add clearance checkbox to product edit page.
 *
 * Fired by `woocommerce_product_options_general_product_data`.
 *
 * @internal WordPress action hook
 */
function add_product_checkbox_hook(): void {
	global $post;

	try {
		$is_clearance = $post ? is_clearance( wc_get_product( $post->ID ) ) : false;
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error( 'Could not add clearance section checkbox: ' . $e->getMessage() );
		return;
	}

	echo '<div class="wc-clearance-status-panel">';
	woocommerce_wp_checkbox(
		array(
			'id'          => 'wc-clearance-status',
			'label'       => __( 'Clearance section', 'wc-clearance' ),
			'description' => __( 'Include in clearance section', 'wc-clearance' ),
			'value'       => $is_clearance ? 'clearance' : '',
			'cbvalue'     => 'clearance',
		)
	);

	// Custom help text under the field.
	$settings_url = admin_url( 'admin.php' ); // todo: add link to settings page when it exists.

	$link = settings_screen_enabled()
		? sprintf(
			' <a href="%s" style="text-decoration:none;">%s</a>',
			esc_url( $settings_url ),
			esc_html__( 'Edit settings', 'wc-clearance' )
		)
		: '';

	printf(
		'<div class="wc-clearance-status-help">%s%s</div><!-- .wc-clearance-status-help -->',
		esc_html__( 'Included products appear in the store’s clearance section and display a badge.', 'wc-clearance' ),
		wp_kses_post( $link )
	);
	echo '</div><!-- .wc-clearance-status-panel -->';
}

/**
 * Save clearance checkbox value.
 *
 * Fired by `woocommerce_admin_process_product_object`.
 *
 * @param \WC_Product $product The product being saved.
 * @internal WordPress action hook
 */
function save_product_checkbox_hook( \WC_Product $product ): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$is_clearance = isset( $_POST['wc-clearance-status'] );
	try {
		set_clearance( $product, $is_clearance );
	} catch ( \Throwable $e ) {
		$product_id = $product instanceof \WC_Product ? $product->get_id() : null;
		\wc_get_logger()->error(
			'Could not save clearance status for product ID ' . $product_id .
			' with desired status ' . ( $is_clearance ? 'true' : 'false' ) . ': ' . $e->getMessage(),
			array(
				'product_id'        => $product_id,
				'desired_clearance' => $is_clearance,
			)
		);
	}
}
