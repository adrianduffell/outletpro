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
const ONBOARDING_NOTICE_STORAGE_KEY = 'wc_clearance_product_onboarding_dismissed';

/**
 * Key used in localStorage to persist the publish page notice dismissal.
 */
const PUBLISH_PAGE_NOTICE_STORAGE_KEY = 'wc_clearance_publish_page_notice_dismissed';

/**
 * Helper to initialize admin product list table features.
 *
 * @since 1.0.0
 */
function init_admin_product_list_table(): void {
	add_action( 'admin_notices', 'WC_Clearance\product_onboarding_notice_hook' );
}

/**
 * Display the appropriate admin notice on the product list screen.
 *
 * Shows the onboarding notice when the clearance section is empty, or the
 * publish page notice when there are products in the clearance section but
 * the page is not yet published.
 *
 * Fired by `admin_notices`.
 *
 * @internal WordPress action hook
 */
function product_onboarding_notice_hook(): void {
	$screen = get_current_screen();

	if ( ! $screen instanceof \WP_Screen || 'edit-product' !== $screen->id ) {
		return;
	}

	try {
		$is_empty = clearance_section_empty();
	} catch ( \RuntimeException $e ) {
		return;
	}

	if ( $is_empty ) {
		if ( ! current_user_can( 'edit_products' ) ) {
			return;
		}

		$notice_type = 'notice-info';
		$css_class   = 'wc-clearance-onboarding-notice';
		$storage_key = ONBOARDING_NOTICE_STORAGE_KEY;
		$content     = '<p><strong>' . esc_html__( 'Clearance section', 'wc-clearance' ) . '</strong> <span class="wc-clearance-new">' . esc_html__( 'New', 'wc-clearance' ) . '</span></p>' .
			'<p>' . __( 'Include products in the clearance section to promote them in your store. Edit a product and find the clearance section field in <strong>Product data</strong> → <strong>General</strong>.', 'wc-clearance' ) . '</p>';
	} else {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		try {
			$page_id = get_clearance_page_id();
		} catch ( \UnexpectedValueException $e ) {
			return;
		}

		if ( null === $page_id ) {
			return;
		}
		$page = get_post( $page_id );

		if ( ! $page instanceof \WP_Post || 'publish' === $page->post_status ) {
			return;
		}

		$edit_link   = get_edit_post_link( $page_id );
		$notice_type = 'notice-warning';
		$css_class   = 'wc-clearance-publish-page-notice';
		$storage_key = PUBLISH_PAGE_NOTICE_STORAGE_KEY;
		$content     = '<p>' . sprintf(
			/* translators: %s URL to edit the clearance section page */
			__( 'Publish the clearance section page to help customers find those products. <a href="%s">Publish now</a>', 'wc-clearance' ),
			esc_url( $edit_link )
		) . '</p>';
	}

	?>
	<div class="notice <?php echo esc_attr( $notice_type ); ?> is-dismissible <?php echo esc_attr( $css_class ); ?>">
		<?php echo wp_kses_post( $content ); ?>
	</div>
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
			notice.classList.add('is-visible');

			var handler = function( event ) {
				if ( ! event.target.closest('.notice-dismiss') ) {
					return;
				}

				try {
					localStorage.setItem( storageKey, '1' );
				} catch ( e ) {}
				notice.classList.remove('is-visible');
			};

			notice.addEventListener('click', handler);
		}
	}() );
	</script>
	<?php
}
