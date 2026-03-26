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
	add_action( 'admin_notices', 'WC_Clearance\product_publish_page_notice_hook' );
	add_action( 'admin_notices', 'WC_Clearance\product_clearance_complete_notice_hook' );
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

	try {
		if ( ! clearance_section_empty() ) {
			return;
		}
	} catch ( \RuntimeException $e ) {
		return;
	}

	// Check if the clearance page is published for the checklist status.
	$page_published = false;
	try {
		$page_published = clearance_page_is_published();
	} catch ( \RuntimeException $e ) {
		// If the page status cannot be determined, treat as not published.
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
		<ul class="wc-clearance-checklist">
			<li class="wc-clearance-checklist-item">
				<span class="wc-clearance-checklist-icon" aria-hidden="true"><?php echo esc_html( '☐' ); ?></span>
				<?php esc_html_e( 'Include products in the clearance section', 'wc-clearance' ); ?>
			</li>
			<li class="wc-clearance-checklist-item<?php echo esc_attr( $page_published ? ' wc-clearance-checklist-item--checked' : '' ); ?>">
				<span class="wc-clearance-checklist-icon" aria-hidden="true"><?php echo esc_html( $page_published ? '✓' : '☐' ); ?></span>
				<?php esc_html_e( 'Publish the clearance section page', 'wc-clearance' ); ?>
			</li>
		</ul>
	</div>
	<script>
	( function() {
		var storageKey = <?php echo wp_json_encode( ONBOARDING_DISMISS_KEY ); ?>;
		try {
			if ( localStorage.getItem( storageKey ) ) {
				// Notice has been dismissed, do not show.
				return;
			}
		} catch ( e ) {
			// localStorage unavailable (e.g. privacy mode), do not show.
			return;
		}

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

/**
 * Display the publish page notice on the product list screen.
 *
 * Shows when there are products in the clearance section but the
 * clearance section page is not yet published.
 *
 * Fired by `admin_notices`.
 *
 * @internal WordPress action hook
 */
function product_publish_page_notice_hook(): void {
	$screen = get_current_screen();

	if ( ! $screen instanceof \WP_Screen || 'edit-product' !== $screen->id ) {
		return;
	}

	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	try {
		if ( clearance_section_empty() ) {
			return;
		}
		$page_id = get_clearance_page_id();
	} catch ( \RuntimeException | \UnexpectedValueException $e ) {
		return;
	}

	if ( null === $page_id ) {
		return;
	}
	$page = get_post( $page_id );

	if ( ! $page instanceof \WP_Post || 'publish' === $page->post_status ) {
		return;
	}

	$edit_link = get_edit_post_link( $page_id );

	?>
	<div class="notice notice-info is-dismissible wc-clearance-publish-page-notice">
		<p><strong><?php esc_html_e( 'Clearance section', 'wc-clearance' ); ?></strong> <span class="wc-clearance-new"><?php esc_html_e( 'New', 'wc-clearance' ); ?></span></p>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s URL to edit the clearance section page */
					__( 'Publish the clearance section page to help customers find those products. <a href="%s">Edit page</a>', 'wc-clearance' ),
					esc_url( $edit_link )
				)
			);
			?>
		</p>
		<ul class="wc-clearance-checklist">
			<li class="wc-clearance-checklist-item wc-clearance-checklist-item--checked">
				<span class="wc-clearance-checklist-icon" aria-hidden="true">&#10003;</span>
				<?php esc_html_e( 'Include products in the clearance section', 'wc-clearance' ); ?>
			</li>
			<li class="wc-clearance-checklist-item">
				<span class="wc-clearance-checklist-icon" aria-hidden="true">&#9744;</span>
				<?php esc_html_e( 'Publish the clearance section page', 'wc-clearance' ); ?>
			</li>
		</ul>
	</div>
	<script>
	( function() {
		var storageKey = <?php echo wp_json_encode( ONBOARDING_DISMISS_KEY ); ?>;
		try {
			if ( localStorage.getItem( storageKey ) ) {
				// Notice has been dismissed, do not show.
				return;
			}
		} catch ( e ) {
			// localStorage unavailable (e.g. privacy mode), do not show.
			return;
		}

		var notice = document.querySelector('.wc-clearance-publish-page-notice');

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

/**
 * Display the clearance complete notice on the product list screen.
 *
 * Shows when there are products in the clearance section and the
 * clearance section page is published.
 *
 * Fired by `admin_notices`.
 *
 * @internal WordPress action hook
 */
function product_clearance_complete_notice_hook(): void {
	$screen = get_current_screen();

	if ( ! $screen instanceof \WP_Screen || 'edit-product' !== $screen->id ) {
		return;
	}

	if ( ! current_user_can( 'edit_products' ) ) {
		return;
	}

	try {
		if ( clearance_section_empty() ) {
			return;
		}
		if ( ! clearance_page_is_published() ) {
			return;
		}
	} catch ( \RuntimeException $e ) {
		return;
	}

	?>
	<div class="notice notice-success is-dismissible wc-clearance-complete-notice">
		<p><strong><?php esc_html_e( 'Clearance section', 'wc-clearance' ); ?></strong> <span class="wc-clearance-new"><?php esc_html_e( 'New', 'wc-clearance' ); ?></span></p>
		<p><?php esc_html_e( 'Clearance section is ready. Tip: add the clearance section page to a menu or create a link to promote it in your store.', 'wc-clearance' ); ?></p>
		<ul class="wc-clearance-checklist">
			<li class="wc-clearance-checklist-item wc-clearance-checklist-item--checked">
				<span class="wc-clearance-checklist-icon" aria-hidden="true">&#10003;</span>
				<?php esc_html_e( 'Include products in the clearance section', 'wc-clearance' ); ?>
			</li>
			<li class="wc-clearance-checklist-item wc-clearance-checklist-item--checked">
				<span class="wc-clearance-checklist-icon" aria-hidden="true">&#10003;</span>
				<?php esc_html_e( 'Publish the clearance section page', 'wc-clearance' ); ?>
			</li>
		</ul>
	</div>
	<script>
	( function() {
		var storageKey = <?php echo wp_json_encode( ONBOARDING_DISMISS_KEY ); ?>;
		try {
			if ( localStorage.getItem( storageKey ) ) {
				// Notice has been dismissed, do not show.
				return;
			}
		} catch ( e ) {
			// localStorage unavailable (e.g. privacy mode), do not show.
			return;
		}

		var notice = document.querySelector('.wc-clearance-complete-notice');

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
