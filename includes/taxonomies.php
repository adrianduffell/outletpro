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
 */
function init_taxonomies(): void {
	register_clearance_status_taxonomy();
}

/**
 * Register the clearance status taxonomy.
 *
 */
function register_clearance_status_taxonomy(): void {
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
