<?php
/**
 * Admin product bulk edit functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize product bulk edit features.
 *
 * @internal
 */
function init_admin_product_bulk_edit(): void {
	add_action( 'woocommerce_product_bulk_edit_end', 'WC_Clearance\bulk_edit_field_hook' );
	add_action( 'woocommerce_product_bulk_edit_save', 'WC_Clearance\save_bulk_edit_hook' );
}

/**
 * Render the clearance section field in the bulk edit form.
 *
 * Fired by `woocommerce_product_bulk_edit_end`.
 *
 * @internal WordPress action hook
 */
function bulk_edit_field_hook(): void {
	?>
	<div class="inline-edit-group">
		<label class="alignleft">
			<span class="title wc-clearance-bulk-edit-title"><?php esc_html_e( 'Clearance section', 'wc-clearance' ); ?></span>
			<select name="wc_clearance_bulk">
				<option value=""><?php esc_html_e( '— No change —', 'wc-clearance' ); ?></option>
				<option value="yes"><?php esc_html_e( 'Include', 'wc-clearance' ); ?></option>
				<option value="no"><?php esc_html_e( 'Remove', 'wc-clearance' ); ?></option>
			</select>
		</label>
	</div>
	<?php
}

/**
 * Save clearance section status during bulk edit.
 *
 * Fired by `woocommerce_product_bulk_edit_save`.
 *
 * @param \WC_Product $product The product being saved.
 * @internal WordPress action hook
 */
function save_bulk_edit_hook( \WC_Product $product ): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $_REQUEST['wc_clearance_bulk'] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$value = wc_clean( wp_unslash( $_REQUEST['wc_clearance_bulk'] ) );

	if ( 'yes' === $value ) {
		try {
			add_to_clearance( $product );
		} catch ( \Throwable $e ) {
			\wc_get_logger()->error(
				'Could not add product ID ' . $product->get_id() . ' to clearance: ' . $e->getMessage(),
				array( 'product_id' => $product->get_id() )
			);
		}
	} elseif ( 'no' === $value ) {
		try {
			remove_from_clearance( $product );
		} catch ( \Throwable $e ) {
			\wc_get_logger()->error(
				'Could not remove product ID ' . $product->get_id() . ' from clearance: ' . $e->getMessage(),
				array( 'product_id' => $product->get_id() )
			);
		}
	}
}
