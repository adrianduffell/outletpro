<?php
/**
 * System status functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Add clearance section info to the WooCommerce system status report.
 *
 * @since 1.0.0
 */
function add_system_status_section(): void {
	global $wpdb;

	$taxonomy_registered = taxonomy_exists( CLEARANCE_STATUS_TAXONOMY );

	$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

	$clearance_product_count = 0;
	if ( $canonical_term ) {
		$clearance_product_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p.ID)
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE tt.term_id = %d
				AND tt.taxonomy = %s
				AND p.post_type = 'product'
				AND p.post_status = 'publish'",
				$canonical_term->term_id,
				CLEARANCE_STATUS_TAXONOMY
			)
		);
	}
	?>
	<table class="wc_status_table widefat" cellspacing="0">
		<thead>
			<tr>
				<th colspan="3" data-export-label="Clearance Section">
					<h2><?php esc_html_e( 'Clearance Section', 'wc-clearance' ); ?></h2>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td data-export-label="Clearance status taxonomy registered"><?php esc_html_e( 'Clearance status taxonomy registered:', 'wc-clearance' ); ?></td>
				<td class="help"></td>
				<td data-testid="clearance-taxonomy-registered"><?php echo $taxonomy_registered ? esc_html__( 'yes', 'wc-clearance' ) : esc_html__( 'no', 'wc-clearance' ); ?></td>
			</tr>
			<tr>
				<td data-export-label="Clearance status canonical term ID"><?php esc_html_e( 'Clearance status canonical term ID:', 'wc-clearance' ); ?></td>
				<td class="help"></td>
				<td data-testid="clearance-canonical-term-id">
					<?php
					if ( $canonical_term ) {
						echo esc_html( $canonical_term->term_id );
					} else {
						?>
						<mark class="error"><span><?php esc_html_e( 'Canonical term not found.', 'wc-clearance' ); ?></span></mark>
						<?php
					}
					?>
				</td>
			</tr>
			<tr>
				<td data-export-label="Total products in clearance section"><?php esc_html_e( 'Total products in clearance section:', 'wc-clearance' ); ?></td>
				<td class="help"></td>
				<td data-testid="clearance-product-count"><?php echo esc_html( $clearance_product_count ); ?></td>
			</tr>
		</tbody>
	</table>
	<?php
}
