<?php
/**
 * Functions for seeding default data.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Seed default taxonomy terms.
 *
 * @throws \Exception If the default term already exists or if term insertion fails.
 */
function seed_default_taxonomy_terms(): void {
	if ( \term_exists( 'clearance-item', 'wc_clearance_status' ) ) {
		throw new \Exception( 'Default term "clearance-item" already exists in taxonomy "wc_clearance_status".' );
	}

	$result = \wp_insert_term(
		'clearance-item',
		'wc_clearance_product_status',
		array(
			'slug'        => 'clearance-item',
			'description' => __( 'Products included in the clearance section.', 'wc-clearance' ),
		)
	);

	if ( is_wp_error( $result ) ) {
		throw new \Exception( 'Failed to insert default term "clearance-item" into taxonomy "wc_clearance_status".' );
	}
}
