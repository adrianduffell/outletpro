<?php
/**
 * Onboarding notice functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * User meta key for tracking whether the onboarding notice has been dismissed.
 */
const ONBOARDING_NOTICE_DISMISSED_META = 'wc_clearance_onboarding_dismissed';

/**
 * Helper to initialize the onboarding notice.
 *
 * @since 1.0.0
 */
function init_onboarding_notice(): void {
	add_action( 'admin_notices', 'WC_Clearance\add_onboarding_notice_hook' );
	add_action( 'wp_ajax_wc_clearance_dismiss_onboarding_notice', 'WC_Clearance\dismiss_onboarding_notice_hook' );
}

/**
 * Determine whether the onboarding notice should be shown for the current user.
 *
 * Returns true when the user has not dismissed the notice and no products
 * have been included in the clearance section yet.
 */
function should_show_onboarding_notice(): bool {
	if ( get_user_meta( get_current_user_id(), ONBOARDING_NOTICE_DISMISSED_META, true ) ) {
		return false;
	}

	try {
		return 0 === count_clearance();
	} catch ( \Throwable $e ) {
		return false;
	}
}

/**
 * Display the onboarding notice on the product screen.
 *
 * Fired by `admin_notices`.
 *
 * @internal WordPress action hook
 */
function add_onboarding_notice_hook(): void {
	$screen = get_current_screen();

	if ( ! $screen instanceof \WP_Screen || 'edit-product' !== $screen->id ) {
		return;
	}

	if ( ! current_user_can( 'edit_products' ) ) {
		return;
	}

	if ( ! should_show_onboarding_notice() ) {
		return;
	}

	$nonce    = wp_create_nonce( 'wc_clearance_dismiss_onboarding_notice' );
	$ajax_url = admin_url( 'admin-ajax.php' );
	?>
	<div class="notice notice-info is-dismissible wc-clearance-onboarding-notice">
		<p><strong><?php esc_html_e( 'New: Clearance section', 'wc-clearance' ); ?></strong></p>
		<p>
			<?php esc_html_e( 'Include products in the clearance section to promote them in your store.', 'wc-clearance' ); ?>
			<a href="https://adrianduffell.com/wc-clearance/"><?php esc_html_e( 'Learn more', 'wc-clearance' ); ?></a>
		</p>
	</div>
	<script>
	( function() {
		var notice = document.querySelector( '.wc-clearance-onboarding-notice' );
		if ( ! notice ) {
			return;
		}
		notice.addEventListener( 'click', function( event ) {
			if ( ! event.target.closest( '.notice-dismiss' ) ) {
				return;
			}
			var xhr = new XMLHttpRequest();
			xhr.open( 'POST', <?php echo wp_json_encode( $ajax_url ); ?> );
			xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
			xhr.send( 'action=wc_clearance_dismiss_onboarding_notice&_wpnonce=' + encodeURIComponent( <?php echo wp_json_encode( $nonce ); ?> ) );
		} );
	}() );
	</script>
	<?php
}

/**
 * AJAX handler to dismiss the onboarding notice for the current user.
 *
 * Fired by `wp_ajax_wc_clearance_dismiss_onboarding_notice`.
 *
 * @internal WordPress action hook
 */
function dismiss_onboarding_notice_hook(): void {
	check_ajax_referer( 'wc_clearance_dismiss_onboarding_notice' );
	update_user_meta( get_current_user_id(), ONBOARDING_NOTICE_DISMISSED_META, '1' );
	wp_die();
}
