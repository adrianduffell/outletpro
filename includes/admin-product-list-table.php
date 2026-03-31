<?php
/**
 * Admin product list table functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Key used in localStorage to persist the onboarding notice dismissal.
 */
const ONBOARDING_DISMISS_STORAGE_KEY = 'wc_clearance_product_onboarding_dismissed';

/**
 * Helper to initialize admin product list table features.
 *
 * @since 1.0.0
 */
function init_admin_product_list_table(): void {
	add_action( 'admin_notices', 'WC_Clearance\product_onboarding_notice_hook' );
}

/**
 * Display the appropriate admin notice on products admin pages.
 *
 * When the clearance page is configured, shows a unified notice with a setup
 * progress checklist. When no page is configured, shows a simpler notice.
 *
 * Fired by `admin_notices`.
 *
 * @internal WordPress action hook
 */
function product_onboarding_notice_hook(): void {
	$screen = get_current_screen();

	if ( ! $screen instanceof \WP_Screen || ! in_array( $screen->id, array( 'edit-product', 'product' ), true ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_products' ) ) {
		return;
	}

	try {
		$is_empty = clearance_section_empty();
	} catch ( \RuntimeException $e ) {
		return;
	}

	// Try to get the clearance page ID; treat a corrupted value the same as no page.
	try {
		$page_id = get_clearance_page_id();
	} catch ( \UnexpectedValueException $e ) {
		$page_id = null;
	}

	if ( null !== $page_id ) {
		// Page is configured — show a checklist notice that tracks setup progress.
		$page         = get_post( $page_id );
		$is_published = $page instanceof \WP_Post && 'publish' === $page->post_status;
		render_clearance_checklist_notice( $is_empty, $is_published, $page_id );
	} else {
		// No page configured — show a simpler informational notice.
		render_clearance_simple_notice( $is_empty );
	}
}

/**
 * Render a checklist notice showing setup progress for the clearance section.
 *
 * @param bool $is_empty     Whether the clearance section has no products yet.
 * @param bool $is_published Whether the clearance section page is published.
 * @param int  $page_id      Post ID of the clearance section page.
 */
function render_clearance_checklist_notice( bool $is_empty, bool $is_published, int $page_id ): void {
	$products_done = ! $is_empty;
	$page_done     = $is_published;

	if ( ! $products_done ) {
		$message = __( "Welcome! Let's set up with a few short steps.</p> <p>Include a product in the clearance section by adding or editing a product, then find the clearance section field in <strong>Product data</strong> → <strong>General</strong>.", 'wc-clearance' );
	} elseif ( ! $page_done ) {
		$edit_url  = get_edit_post_link( $page_id );
		$edit_link = $edit_url ? ' <a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit page', 'wc-clearance' ) . '</a>' : '';
		$message   = __( 'Great, products are included in clearance section! </p><p>There’s a clearance section page now added to help customers find these products in one place. Make any changes and publish it to finish setting up.', 'wc-clearance' ) . $edit_link;
	} else {
		$message = __( 'Fantastic, the clearance section is ready! Tip: promote it in your store by creating a link to the clearance section page or adding it to the navigation.', 'wc-clearance' );
	}

	?>
	<div class="notice notice-info is-dismissible wc-clearance-onboarding-notice">
		<h3><?php esc_html_e( 'Clearance section', 'wc-clearance' ); ?> <span class="wc-clearance-new"><?php esc_html_e( 'New', 'wc-clearance' ); ?></span></h3>
		<p><?php echo wp_kses_post( $message ); ?></p>
		<p><strong><?php esc_html_e( 'Setup progress', 'wc-clearance' ); ?></strong></p>
		<ul class="wc-clearance-checklist">
			<li>
				<?php if ( $products_done ) : ?>
					<span aria-hidden="true">✓</span><span class="screen-reader-text"><?php esc_html_e( 'Complete:', 'wc-clearance' ); ?></span>
				<?php else : ?>
					<span aria-hidden="true">☐</span><span class="screen-reader-text"><?php esc_html_e( 'Incomplete:', 'wc-clearance' ); ?></span>
				<?php endif; ?>
				<?php esc_html_e( 'Include products in the clearance section', 'wc-clearance' ); ?>
			</li>
			<li>
				<?php if ( $page_done ) : ?>
					<span aria-hidden="true">✓</span><span class="screen-reader-text"><?php esc_html_e( 'Complete:', 'wc-clearance' ); ?></span>
				<?php else : ?>
					<span aria-hidden="true">☐</span><span class="screen-reader-text"><?php esc_html_e( 'Incomplete:', 'wc-clearance' ); ?></span>
				<?php endif; ?>
				<?php esc_html_e( 'Publish the clearance section page', 'wc-clearance' ); ?>
			</li>
		</ul>
	</div>
	<?php render_notice_dismissal_script( ONBOARDING_DISMISS_STORAGE_KEY, 'wc-clearance-onboarding-notice' ); ?>
	<?php
}

/**
 * Render a simple informational notice when no clearance page is configured.
 *
 * @param bool $is_empty Whether the clearance section has no products yet.
 */
function render_clearance_simple_notice( bool $is_empty ): void {
	if ( $is_empty ) {
		$content = '<h3>' . esc_html__( 'Clearance section', 'wc-clearance' ) . ' <span class="wc-clearance-new">' . esc_html__( 'New', 'wc-clearance' ) . '</span></h3>' .
			'<p>' . __( "Welcome! Let's get started by including products in the clearance section. Add or edit a product, and find the clearance section field in <strong>Product data</strong> → <strong>General</strong>.", 'wc-clearance' ) . '</p>';
	} else {
		$content = '<h3>' . esc_html__( 'Clearance section', 'wc-clearance' ) . ' <span class="wc-clearance-new">' . esc_html__( 'New', 'wc-clearance' ) . '</span></h3>' .
			'<p>' . esc_html__( 'Fantastic, products are included in clearance section! Tip: display iton your store using the clearance section block.', 'wc-clearance' ) . '</p>';
	}

	?>
	<div class="notice notice-info is-dismissible wc-clearance-onboarding-notice">
		<?php echo wp_kses_post( $content ); ?>
	</div>
	<?php render_notice_dismissal_script( ONBOARDING_DISMISS_STORAGE_KEY, 'wc-clearance-onboarding-notice' ); ?>
	<?php
}

/**
 * Render the inline script that handles localStorage-based notice dismissal.
 *
 * @param string $storage_key localStorage key used to persist the dismissal state.
 * @param string $css_class   CSS class of the notice element.
 */
function render_notice_dismissal_script( string $storage_key, string $css_class ): void {
	?>
	<script>
	( function() {
		var storageKey = <?php echo wp_json_encode( $storage_key ); ?>;
		var noticeClass = <?php echo wp_json_encode( $css_class ); ?>;
		try {
			if ( localStorage.getItem( storageKey ) ) {
				// Notice has been dismissed, do not show.
				return;
			}
		} catch ( e ) {
			// localStorage unavailable (e.g. privacy mode), do not show.
			return;
		}

		var notice = document.querySelector( '.' + noticeClass );

		if ( notice ) {
			notice.classList.add( 'is-visible' );

			var handler = function( event ) {
				if ( ! event.target.closest( '.notice-dismiss' ) ) {
					return;
				}

				try {
					localStorage.setItem( storageKey, '1' );
				} catch ( e ) {}
				notice.classList.remove( 'is-visible' );
			};

			notice.addEventListener( 'click', handler );
		}
	}() );
	</script>
	<?php
}
