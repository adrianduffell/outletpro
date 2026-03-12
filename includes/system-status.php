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
 * Get the clearance section page, or null if not configured or not found.
 */
function get_clearance_section_page(): ?\WP_Post {
	try {
		if ( ! clearance_page_exists() ) {
			return null;
		}
	} catch ( \UnexpectedValueException $e ) {
		// Corrupted option value: treat as not found.
		return null;
	}
	$page = get_post( (int) get_option( CLEARANCE_PAGE_OPTION ) );
	return $page instanceof \WP_Post ? $page : null;
}

/**
 * Render the clearance section page cell content.
 *
 * @internal
 *
 * @param \WP_Post|null $page The clearance section page, or null if not found.
 */
function render_clearance_section_page_cell( ?\WP_Post $page ): void {
	if ( $page ) {
		echo '<a href="' . esc_url( get_edit_post_link( $page->ID ) ) . '">' . esc_html( $page->post_title ) . '</a>';
		echo ' (' . esc_html( $page->post_status ) . ')';
	} else {
		?>
		<mark class="error"><span><?php esc_html_e( 'Clearance section page not found.', 'wc-clearance' ); ?></span></mark>
		<?php
	}
}

/**
 * Add clearance section info to the WooCommerce system status report.
 *
 * Fired by `woocommerce_system_status_report`.
 *
 * @internal WordPress action hook
 */
function add_system_status_section_hook(): void {
	$taxonomy_registered = taxonomy_exists( CLEARANCE_STATUS_TAXONOMY );

	$canonical_term = get_term_by( 'name', CLEARANCE_STATUS_CANONICAL_TERM, CLEARANCE_STATUS_TAXONOMY );

	$clearance_product_count = $taxonomy_registered ? count_clearance() : 0;

	$page = get_clearance_section_page();
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
				<td data-testid="clearance-taxonomy-registered"><?php echo $taxonomy_registered ? esc_html__( 'Yes', 'wc-clearance' ) : esc_html__( 'No', 'wc-clearance' ); ?></td>
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
			<tr>
				<td data-export-label="Clearance section page"><?php esc_html_e( 'Clearance section page:', 'wc-clearance' ); ?></td>
				<td class="help"></td>
				<td data-testid="clearance-section-page">
					<?php render_clearance_section_page_cell( $page ); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}
