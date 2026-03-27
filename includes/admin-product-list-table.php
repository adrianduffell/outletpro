<?php
/**
 * Admin product list table functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Key used in localStorage to persist dismissal of any product micro-checklist notice.
 * All three product list notices share this key so that dismissing one dismisses them all.
 */
const ONBOARDING_DISMISS_KEY = 'wc_clearance_onboarding_dismissed';

/**
 * Helper to initialize admin product list table features.
 *
 * @since 1.0.0
 */
function init_admin_product_list_table(): void {
	add_action( 'admin_notices', 'WC_Clearance\product_onboarding_notice_hook' );
}

/**
 * Display the appropriate onboarding notice on the product list screen.
 *
 * Determines which notice to show based on the current clearance setup state:
 * - State 1 (no clearance products): prompt to include products, with checklist.
 * - State 2 (products exist, page not published): prompt to publish the page, with checklist.
 * - State 3 (products exist, page published): clearance complete confirmation, with checklist.
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

	if ( ! current_user_can( 'edit_products' ) ) {
		return;
	}

	try {
		$clearance_section_empty = clearance_section_empty();
	} catch ( \RuntimeException $e ) {
		return;
	}

	$page_published      = false;
	$page_status_unknown = false;
	try {
		$page_published = clearance_page_is_published();
	} catch ( \RuntimeException $e ) {
		\wc_get_logger()->error( 'Could not determine clearance page published status: ' . $e->getMessage() );
		$page_status_unknown = true;
	}

	// Determine which notice state applies and build state-specific variables.
	if ( $clearance_section_empty ) {
		// State 1: No clearance products yet.
		$notice_level      = 'notice-info';
		$notice_type_class = '';
		$body              = wp_kses_post(
			__(
				'Include products in the clearance section to promote them in your store. Edit a product and find the clearance section field in <strong>Product data</strong> → <strong>General</strong>.',
				'wc-clearance'
			)
		);
	} elseif ( ! $page_published && ! $page_status_unknown ) {
		// State 2: Products exist, page not yet published.
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		try {
			$page_id = get_clearance_page_id();
		} catch ( \UnexpectedValueException $e ) {
			return;
		}
		if ( null === $page_id || ! get_post( $page_id ) instanceof \WP_Post ) {
			return;
		}
		$notice_level      = 'notice-info';
		$notice_type_class = 'wc-clearance-publish-page-notice';
		$body              = wp_kses_post(
			sprintf(
				/* translators: %s URL to edit the clearance section page */
				__( 'Publish the clearance section page to help customers find those products. <a href="%s">Edit page</a>', 'wc-clearance' ),
				esc_url( get_edit_post_link( $page_id ) )
			)
		);
	} elseif ( $page_published ) {
		// State 3: Products exist, page published — clearance is complete.
		$notice_level      = 'notice-success';
		$notice_type_class = 'wc-clearance-complete-notice';
		$body              = esc_html__( 'Clearance section is ready. Tip: add the clearance section page to a menu or create a link to promote it in your store.', 'wc-clearance' );
	} else {
		return;
	}

	// Build checklist item state.
	$products_included = ! $clearance_section_empty;
	$products_icon     = $products_included ? '✓' : '☐';
	$products_class    = $products_included ? ' wc-clearance-checklist-item--checked' : '';
	$page_icon         = $page_status_unknown ? '⍰' : ( $page_published ? '✓' : '☐' );
	$page_class        = ( ! $page_status_unknown && $page_published ) ? ' wc-clearance-checklist-item--checked' : '';

	?>
	<div class="notice <?php echo esc_attr( $notice_level ); ?> is-dismissible wc-clearance-onboarding-notice <?php echo esc_attr( $notice_type_class ); ?>">
		<h3><?php esc_html_e( 'Clearance section', 'wc-clearance' ); ?> <span class="wc-clearance-new"><?php esc_html_e( 'New', 'wc-clearance' ); ?></span></h3>
		<p><?php echo wp_kses_post( $body ); ?></p>
		<ul class="wc-clearance-checklist">
			<li class="wc-clearance-checklist-item<?php echo esc_attr( $products_class ); ?>">
				<span class="wc-clearance-checklist-icon" aria-hidden="true"><?php echo esc_html( $products_icon ); ?></span>
				<?php esc_html_e( 'Include products in the clearance section', 'wc-clearance' ); ?>
			</li>
			<li class="wc-clearance-checklist-item<?php echo esc_attr( $page_class ); ?>">
				<span class="wc-clearance-checklist-icon" aria-hidden="true"><?php echo esc_html( $page_icon ); ?></span>
				<?php esc_html_e( 'Publish the clearance section page', 'wc-clearance' ); ?>
			</li>
		</ul>
	</div>
	<script>
	( function() {
		var storageKey = <?php echo wp_json_encode( ONBOARDING_DISMISS_KEY ); ?>;
		try {
			if ( localStorage.getItem( storageKey ) ) {
				return;
			}
		} catch ( e ) {
			return;
		}

		var notice = document.querySelector( '.wc-clearance-onboarding-notice' );

		if ( notice ) {
			notice.classList.add( 'is-visible' );
			notice.addEventListener( 'click', function ( event ) {
				if ( ! event.target.closest( '.notice-dismiss' ) ) {
					return;
				}
				try {
					localStorage.setItem( storageKey, '1' );
				} catch ( e ) {}
				notice.classList.remove( 'is-visible' );
			} );
		}
	}() );
	</script>
	<?php
}
