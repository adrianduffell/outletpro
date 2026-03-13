<?php
/**
 * System status functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Helper to initialize system status.
 *
 * @since 1.0.0
 */
function init_system_status(): void {
	add_action( 'woocommerce_system_status_report', 'WC_Clearance\add_system_status_section_hook', 99 );
}

/**
 * Add clearance section info to the WooCommerce system status report.
 *
 * Fired by `woocommerce_system_status_report`.
 *
 * @internal WordPress action hook
 */
function add_system_status_section_hook(): void {
	echo '<table class="wc_status_table widefat" cellspacing="0">';
	echo '<thead><tr><th colspan="3" data-export-label="Clearance Section">	<h2>' . esc_html__( 'Clearance Section', 'wc-clearance' ) . '</h2></th></tr></thead><tbody>';

	$report_items = array_merge( report_taxonomies(), report_page() );

	foreach ( $report_items as $id => $report_item ) {
		$label = $report_item[0];
		$value = $report_item[1];

		printf(
			'<tr><td>%1$s</td><td class="help"></td><td data-testid="%3$s">%2$s</td></tr>',
			esc_html( (string) $label ),
			// Special handling for the canonical term ID item to highlight the error state.
			// todo: consider generalising this for other items.
			( 'Not found' === $value && str_contains( $label, 'Canonical term ID' ) ? '<mark class="error"><span>Canonical term not found.</span></mark>' : esc_html( (string) $value ) ),
			esc_attr( $id )
		);
	}

	echo '</tbody></table>';
}
