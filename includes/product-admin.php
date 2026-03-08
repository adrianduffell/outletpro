<?php
/**
 * Product admin functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Render the clearance checkbox in the product general data tab.
 *
 * @since 1.0.0
 */
function render_clearance_checkbox(): void {
	global $post;

	if ( ! $post ) {
		return;
	}

	$product = wc_get_product( $post->ID );
	$checked = $product && taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) && is_clearance( $product );

	woocommerce_wp_checkbox(
		array(
			'id'          => 'wc_clearance',
			'label'       => __( 'Include in clearance section', 'wc-clearance' ),
			'description' => __( 'Products in your clearance section appear on the clearance page and display a badge.', 'wc-clearance' ),
			'value'       => $checked ? 'yes' : '',
			'cbvalue'     => 'yes',
		)
	);
}

/**
 * Save the clearance checkbox state for a product.
 *
 * @param int $product_id The ID of the product being saved.
 * @since 1.0.0
 */
function save_clearance_checkbox( int $product_id ): void {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified by WooCommerce before this hook fires.
	$is_checked = isset( $_POST['wc_clearance'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['wc_clearance'] ) );

	try {
		if ( $is_checked ) {
			add_to_clearance( $product );
		} else {
			remove_from_clearance( $product );
		}
	} catch ( \RuntimeException $e ) {
		wc_get_logger()->error( $e->getMessage() );
	}
}
