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
 * @since 1.0.0
 */
function init_admin_product_options(): void {
	add_action( 'woocommerce_product_options_general_product_data', __NAMESPACE__ . '\hook_add_product_checkbox' );
	add_action( 'woocommerce_admin_process_product_object', __NAMESPACE__ . '\hook_save_product_checkbox' );
}

/**
 * Add clearance checkbox to product edit page
 */
function hook_add_product_checkbox(): void {
	global $post;

	$is_clearance = $post ? is_clearance( wc_get_product( $post->ID ) ) : false;

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
	$settings_url = admin_url( 'admin.php?page=wc-clearance' );
	printf(
		'<div class="wc-clearance-status-help">%1$s <a href="%2$s" style="text-decoration:none;">%3$s</a></div><!-- .wc-clearance-status-help --></div><!-- .wc-clearance-status-panel -->',
		esc_html__( 'Included products appear in the store’s clearance section and display a&nbsp;badge.', 'wc-clearance' ),
		esc_url( $settings_url ),
		esc_html__( 'Edit&nbsp;settings', 'wc-clearance' )
	);
	echo '</div><!-- .wc-clearance-status-panel -->';
}

/**
 * Save clearance checkbox value
 *
 * @param \WC_Product $product The product being saved.
 */
function hook_save_product_checkbox( \WC_Product $product ): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$is_clearance = isset( $_POST['wc-clearance-status'] );

	if ( $is_clearance ) {
		add_to_clearance( $product );
	} else {
		remove_from_clearance( $product );
	}
}
