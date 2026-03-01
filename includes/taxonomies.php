<?php
/**
 * Taxonomy-related functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

const CLEARANCE_STATUS_TAXONOMY = 'wc_clearance_status';

/**
 * Helper to initialize taxonomies.
 *
 * @since 1.0.0
 */
function init_taxonomies(): void {
	register_taxonomy_for_clearance_status();
}

/**
 * Register the clearance status taxonomy.
 *
 * @since 1.0.0
 *
 * @throws \RuntimeException If the taxonomy registration fails.
 */
function register_taxonomy_for_clearance_status(): void {
	$args = array(
		'label'        => __( 'Clearance Status', 'wc-clearance' ),
		'public'       => false,
		'show_ui'      => false,
		'show_in_rest' => false,
		'hierarchical' => false,
		'query_var'    => false,
		'rewrite'      => false,
		'capabilities' => array(
			'assign_terms' => 'edit_products',
			'manage_terms' => 'manage_product_terms',
			'edit_terms'   => 'manage_product_terms',
			'delete_terms' => 'manage_product_terms',
		),
		'meta_box_cb'  => false,
	);

	register_taxonomy( CLEARANCE_STATUS_TAXONOMY, 'product', $args );
}
