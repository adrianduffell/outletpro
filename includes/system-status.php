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

	foreach ( array_merge( report_taxonomies(), report_page() ) as $id => $report_item ) {
		printf(
			'<tr><td>%1$s</td><td class="help"></td><td data-testid="%3$s">%2$s</td></tr>',
			esc_html( (string) $report_item[0] ),
			$report_item[1], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_attr( $id )
		);
	}

	echo '</tbody></table>';
}
