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
 *
 * @since 1.0.0
 */
function init_taxonomies(): void {
	register_clearance_status_taxonomy();
}

/**
 * Register the clearance status taxonomy.
 *
 * @since 1.0.0
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

/**
 * Add products to clearance section.
 *
 * @since 1.0.0
 * @param \WC_Product ...$products Products to update.
 * @throws \RuntimeException If the clearance status taxonomy does not exist.
 */
function add_to_clearance( \WC_Product ...$products ): void {
	if ( ! taxonomy_exists( CLEARANCE_STATUS_TAXONOMY ) ) {
		throw new \RuntimeException( 'Clearance status taxonomy does not exist.' );
	}
	foreach ( $products as $product ) {
		$result = wp_set_object_terms( $product->get_id(), CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

		if ( is_wp_error( $result ) ) {
			throw new \RuntimeException(
				sprintf(
					'Failed to assign clearance status term to product ID %d. %s',
					$product->get_id(),
					$result->get_error_message()
				)
			);
		}

		if ( false === $result ) {
			throw new \RuntimeException(
				sprintf(
					'Failed to assign clearance status term: invalid product ID %d.',
					$product->get_id()
				)
			);
		}
	}
}
