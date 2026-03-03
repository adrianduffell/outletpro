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
	$taxonomy_registered = taxonomy_exists( CLEARANCE_STATUS_TAXONOMY );

	$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

	$clearance_product_count = 0;
	if ( $canonical_term ) {
		$query = new \WP_Query(
			array(
				'post_type'              => 'product',
				'post_status'            => 'publish',
				'fields'                 => 'ids',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'tax_query'              => array(
					array(
						'taxonomy' => CLEARANCE_STATUS_TAXONOMY,
						'field'    => 'term_id',
						'terms'    => $canonical_term->term_id,
					),
				),
			)
		);
		$clearance_product_count = count( $query->posts );
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
