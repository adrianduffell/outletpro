<?php
/**
 * Admin product list table functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Number of days after activation during which the onboarding notice is displayed.
 */
const ONBOARDING_TTL_DAYS = 14;

/**
 * Key used in localStorage to persist the onboarding notice dismissal.
 */
const ONBOARDING_DISMISS_STORAGE_KEY = 'wc_clearance_product_onboarding_dismissed';

/**
 * Helper to initialize admin product list table features.
 *
 * @internal
 */
function init_admin_product_list_table(): void {
	add_action( 'admin_notices', 'WC_Clearance\product_onboarding_notice_hook' );
}

/**
 * Display the appropriate admin notice on products admin pages.
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

	$activated_at = get_option( ACTIVATED_AT_OPTION );
	if ( $activated_at && time() - (int) $activated_at > ONBOARDING_TTL_DAYS * DAY_IN_SECONDS ) {
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

	$new_badge = '<span class="wc-clearance-new">' . esc_html__( 'New', 'wc-clearance' ) . '</span> ';

	if ( $is_empty ) {
		$content = '<p>' . $new_badge .
			'<strong>' . esc_html__( 'Clearance section is empty.', 'wc-clearance' ) . '</strong> ' .
			esc_html__( "Get started by including a product using the checkbox in the product's inventory section.", 'wc-clearance' ) . '</p>';
	} elseif ( null === $page_id || 'trash' === get_post_status( $page_id ) ) {
		$count   = count_clearance();
		$content = '<p>' . $new_badge .
			/* translators: %d: number of products in the clearance section */
			'<strong>' . sprintf( _n( 'Clearance section has %d product.', 'Clearance section has %d products.', $count, 'wc-clearance' ), $count ) . '</strong> ' .
			esc_html__( 'Tip: add it to a page or post using the clearance section block.', 'wc-clearance' ) . '</p>';
	} elseif ( 'publish' !== get_post_status( $page_id ) ) {
		$count     = count_clearance();
		$edit_url  = get_edit_post_link( $page_id );
		$edit_link = $edit_url ? ' <a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit page', 'wc-clearance' ) . '</a>' : '';
		$content   = '<p>' . $new_badge .
			/* translators: %d: number of products in the clearance section */
			'<strong>' . sprintf( _n( 'Clearance section has %d product.', 'Clearance section has %d products.', $count, 'wc-clearance' ), $count ) . '</strong> ' .
			esc_html__( 'Make it visible on the store by editing and publishing the clearance section page.', 'wc-clearance' ) .
			$edit_link . '</p>';
	} else {
		$view_url  = get_permalink( $page_id );
		$view_link = $view_url ? ' <a href="' . esc_url( $view_url ) . '">' . esc_html__( 'View page', 'wc-clearance' ) . '</a>' : '';
		$content   = '<p><span aria-hidden="true">✅</span><span class="screen-reader-text">' . esc_html__( '(complete)', 'wc-clearance' ) . '</span> ' .
			esc_html__( 'Clearance section is ready.', 'wc-clearance' ) .
			$view_link . '</p>';
	}

	?>
<div class="notice notice-info is-dismissible wc-clearance-onboarding-notice">
	<?php echo wp_kses_post( $content ); ?>
</div>
<script>
( function() {
var storageKey = <?php echo wp_json_encode( ONBOARDING_DISMISS_STORAGE_KEY ); ?>;
var noticeClass = 'wc-clearance-onboarding-notice';
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
