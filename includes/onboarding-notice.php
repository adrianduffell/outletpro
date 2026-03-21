<?php
/**
 * Onboarding notice functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Key used in localStorage to persist the notice dismissal.
 */
const ONBOARDING_NOTICE_STORAGE_KEY = 'wc_clearance_onboarding_dismissed';

/**
 * Helper to initialize the onboarding notice.
 *
 * @since 1.0.0
 */
function init_onboarding_notice(): void {
	add_action( 'admin_notices', 'WC_Clearance\add_onboarding_notice_hook' );
}

/**
 * Determine whether the onboarding notice should be shown.
 *
 * Returns true when no products have been included in the clearance section yet.
 */
function should_show_onboarding_notice(): bool {
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

	?>
	<script>
	( function() {
		var storageKey = <?php echo wp_json_encode( ONBOARDING_NOTICE_STORAGE_KEY ); ?>;
		if ( localStorage.getItem( storageKey ) ) {
			var style = document.createElement( 'style' );
			style.textContent = '.wc-clearance-onboarding-notice{display:none}';
			document.head.appendChild( style );
			return;
		}
		document.addEventListener( 'click', function( event ) {
			if ( ! event.target.closest( '.wc-clearance-onboarding-notice .notice-dismiss' ) ) {
				return;
			}
			localStorage.setItem( storageKey, '1' );
		} );
	}() );
	</script>
	<div class="notice notice-info is-dismissible wc-clearance-onboarding-notice">
		<p><strong><?php esc_html_e( 'New: Clearance section', 'wc-clearance' ); ?></strong></p>
		<p>
			<?php esc_html_e( 'Include products in the clearance section to promote them in your store.', 'wc-clearance' ); ?>
			<a href="https://adrianduffell.com/wc-clearance/"><?php esc_html_e( 'Learn more', 'wc-clearance' ); ?></a>
		</p>
	</div>
	<?php
}
