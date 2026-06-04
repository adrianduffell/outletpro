<?php
/**
 * Admin product bulk edit functions.
 *
 * @package WC_Outlet
 */

namespace WC_Outlet;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize product bulk edit features.
 *
 * @internal
 */
function init_admin_product_bulk_edit(): void {
	add_action( 'woocommerce_product_bulk_edit_end', 'WC_Outlet\bulk_edit_field_hook' );
	add_action( 'woocommerce_product_bulk_edit_save', 'WC_Outlet\save_bulk_edit_hook' );
}

/**
 * Render the outlet field in the bulk edit form.
 *
 * Fired by `woocommerce_product_bulk_edit_end`.
 *
 * @internal WordPress action hook
 */
function bulk_edit_field_hook(): void {
	?>
	<div class="inline-edit-group">
		<label class="alignleft">
			<span class="title wc-outlet-bulk-edit-title"><?php esc_html_e( 'Outlet', 'outletpro' ); ?></span>
			<select name="wc_outlet_bulk">
				<option value=""><?php esc_html_e( '— No change —', 'outletpro' ); ?></option>
				<option value="yes"><?php esc_html_e( 'Include', 'outletpro' ); ?></option>
				<option value="no"><?php esc_html_e( 'Remove', 'outletpro' ); ?></option>
			</select>
		</label>
	</div>
	<?php
}

/**
 * Save outlet status during bulk edit.
 *
 * Fired by `woocommerce_product_bulk_edit_save`.
 *
 * @param \WC_Product $product The product being saved.
 * @internal WordPress action hook
 */
function save_bulk_edit_hook( \WC_Product $product ): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $_GET['wc_outlet_bulk'] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$value = sanitize_text_field( wp_unslash( $_GET['wc_outlet_bulk'] ) );

	try {
		if ( 'yes' === $value ) {
			add_to_outlet( $product );
		} elseif ( 'no' === $value ) {
			remove_from_outlet( $product );
		}
	} catch ( \Throwable $e ) {
		\wc_get_logger()->error(
			'Could not update outlet status for product ID ' . $product->get_id() . ': ' . $e->getMessage(),
			array( 'product_id' => $product->get_id() )
		);
	}
}
