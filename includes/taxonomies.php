<?php
/**
 * Taxonomy-related functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Non-public taxonomy used to represent the clearance status of products.
 *
 * Used with a canonical term for internal flagging of products belonging
 * in the clearance section.
 */
const CLEARANCE_STATUS_TAXONOMY = 'wc_clearance_status';

/**
 * Canonical term for products belonging in the clearance section.
 */
const CLEARANCE_STATUS_CANONICAL_TERM = 'clearance';

/**
 * Helper to initialize taxonomies.
 */
function init_taxonomies(): void {
	register_taxonomy_for_clearance_status();
}

/**
 * Register the clearance status taxonomy.
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

/**
 * Seed the clearance status taxonomy with the canonical term.
 *
 * @throws \RuntimeException If the term seeding fails.
 * @since 1.0.0
 */
function seed_clearance_status_taxonomy(): void {
	if ( term_exists( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY ) ) {
		return;
	}

	$result = wp_insert_term( CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

	if ( is_wp_error( $result ) ) {
		throw new \RuntimeException(
			sprintf(
				'Failed to seed clearance status taxonomy. %s',
				$result->get_error_message()
			)
		);
	}
}
