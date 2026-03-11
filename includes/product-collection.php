<?php
/**
 * Product collection functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize the product collection feature.
 *
 * @since 1.0.0
 */
function init_product_collection(): void {
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_product_collection_script' );
}

/**
 * Enqueue the product collection script for the block editor.
 *
 * Only enqueues the script when the canonical clearance term exists so
 * that the collection can be correctly filtered by term ID.
 *
 * @since 1.0.0
 */
function enqueue_product_collection_script(): void {
	$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

	if ( ! $canonical_term ) {
		return;
	}

	$asset_file   = include plugin_dir_path( __FILE__ ) . '../build/index.asset.php';
	$dependencies = array_merge( $asset_file['dependencies'], array( 'wc-blocks-registry' ) );

	wp_register_script(
		'wc-clearance-product-collection',
		plugin_dir_url( __FILE__ ) . '../build/index.js',
		$dependencies,
		$asset_file['version'],
		true
	);

	wp_localize_script(
		'wc-clearance-product-collection',
		'wcClearance',
		array(
			'clearanceTermId' => $canonical_term->term_id,
		)
	);

	wp_enqueue_script( 'wc-clearance-product-collection' );
}
