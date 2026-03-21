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
	add_action( 'admin_enqueue_scripts', 'WC_Clearance\enqueue_onboarding_notice_scripts_hook' );
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
 * Enqueue the onboarding notice dismiss script on the product editing screen.
 *
 * Fired by `admin_enqueue_scripts`.
 *
 * @internal WordPress action hook
 */
function enqueue_onboarding_notice_scripts_hook(): void {
	$screen = get_current_screen();
	if ( ! $screen instanceof \WP_Screen || 'product' !== $screen->id ) {
		return;
	}

	if ( ! should_show_onboarding_notice() ) {
		return;
	}

	wp_enqueue_script(
		'wc-clearance-onboarding-notice',
		plugin_dir_url( __DIR__ ) . 'assets/js/onboarding-notice.js',
		array(),
		VERSION,
		true
	);

	wp_localize_script(
		'wc-clearance-onboarding-notice',
		'wcClearanceOnboarding',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wc_clearance_dismiss_onboarding_notice' ),
		)
	);
}

/**
 * Display the onboarding notice on the product editing screen.
 *
 * Fired by `admin_notices`.
 *
 * @internal WordPress action hook
 */
function add_onboarding_notice_hook(): void {
	$screen = get_current_screen();
	if ( ! $screen instanceof \WP_Screen || 'product' !== $screen->id ) {
		return;
	}

	if ( ! should_show_onboarding_notice() ) {
		return;
	}

	?>
	<div class="notice notice-info is-dismissible wc-clearance-onboarding-notice">
		<p><strong><?php esc_html_e( 'New: Clearance section', 'wc-clearance' ); ?></strong></p>
		<p>
			<?php esc_html_e( 'Include products in the clearance section to promote them in your store.', 'wc-clearance' ); ?>
			<a href="https://adrianduffell.com/wc-clearance/"><?php esc_html_e( 'Learn more', 'wc-clearance' ); ?></a>
		</p>
	</div>
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
