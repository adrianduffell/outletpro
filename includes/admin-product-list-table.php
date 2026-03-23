<?php
/**
 * Admin product list table functions.
 *
 * @package WC_Clearance
 */

namespace WC_Clearance;

defined( 'ABSPATH' ) || exit;

/**
 * Key used in localStorage to persist the notice dismissal.
 */
const ONBOARDING_NOTICE_STORAGE_KEY = 'wc_clearance_product_onboarding_dismissed';

/**
 * Helper to initialize admin product list table features.
 *
 * @since 1.0.0
 */
function init_admin_product_list_table(): void {
	add_action( 'admin_notices', 'WC_Clearance\product_onboarding_notice_hook' );
}

/**
 * Display the onboarding notice on the product list screen.
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

	if ( has_clearance_products() ) {
		return;
	}

	?>
	<div class="notice notice-info is-dismissible wc-clearance-onboarding-notice">
		<p><strong><?php esc_html_e( 'Clearance section', 'wc-clearance' ); ?></strong> <span class="wc-clearance-new"><?php esc_html_e( 'New', 'wc-clearance' ); ?></span></p>
		<p>
			<?php
			echo wp_kses_post(
				__(
					'Include products in the clearance section to promote them in your store. Edit a product and find the clearance section field in <strong>Product data</strong> → <strong>General</strong>.',
					'wc-clearance'
				)
			);
			?>
		</p>
	</div>
	<script>
	( function() {
		var storageKey = <?php echo wp_json_encode( ONBOARDING_NOTICE_STORAGE_KEY ); ?>;
		try {
			if ( localStorage.getItem( storageKey ) ) {
				// Notice has been dismissed, do not show.
				return;
			}
		} catch ( e ) {}

		var notice = document.querySelector('.wc-clearance-onboarding-notice');

		if ( notice ) {
			notice.classList.add('is-visible');

			var handler = function( event ) {
				if ( ! event.target.closest('.notice-dismiss') ) {
					return;
				}

				try {
					localStorage.setItem(storageKey, '1');
				} catch ( e ) {}
				notice.classList.remove('is-visible');
			};

			notice.addEventListener('click', handler);
		}
	}() );
	</script>
	<?php
}
