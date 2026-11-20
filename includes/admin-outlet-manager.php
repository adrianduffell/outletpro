<?php
/**
 * Dedicated outlet page editing screen.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Initialize the dedicated admin screen.
 *
 * @internal
 */
function init_outlet_manager(): void {
	add_action( 'load-post.php', 'OutletPro\\load_outlet_manager_hook' );
}

/**
 * Determine whether a page uses the archive-based outlet screen.
 *
 * @param \WP_Post $post Candidate page.
 * @internal
 */
function uses_outlet_manager( \WP_Post $post ): bool {
	if ( 'page' !== $post->post_type ) {
		return false;
	}
	if ( get_outlet_page_id() !== $post->ID ) {
		return false;
	}
	if ( 'trash' === $post->post_status ) {
		return false;
	}
	return 'outlet-page' !== get_page_template_slug( $post );
}

/**
 * Read one configured tier without exposing the private taxonomy.
 *
 * @param string $slug Canonical tier slug.
 * @return array{id: int, name: string} Tier display data.
 * @throws \RuntimeException If the tier cannot be retrieved.
 * @internal
 */
function get_outlet_manager_tier( string $slug ): array {
	$term = get_term_by( 'slug', $slug, OUTLET_SEARCH_INDEX_TAXONOMY );
	if ( ! $term instanceof \WP_Term ) {
		throw new \RuntimeException( 'Outlet tier could not be retrieved.' );
	}
	return array(
		'id'   => $term->term_id,
		'name' => get_search_index_facet_nice_name( $term ),
	);
}

/**
 * Read configured percentage tiers for the sidebar prototype.
 *
 * @return array<array{id: int, name: string}> Numerically ordered tiers.
 * @internal
 */
function get_outlet_manager_discount_tiers(): array {
	return array_map( static fn( int $amount ): array => get_outlet_manager_tier( 'outlet-discount-' . $amount ), get_discount_tiers() );
}

/**
 * Read configured price thresholds for the sidebar prototype.
 *
 * @return array<array{id: int, name: string}> Numerically ordered bands.
 * @internal
 */
function get_outlet_manager_price_bands(): array {
	return array_map( static fn( int $amount ): array => get_outlet_manager_tier( 'outlet-price-' . $amount ), get_price_tiers() );
}

/**
 * Register replacement after WordPress's early replace_editor screen probe.
 *
 * Fired by `load-post.php`.
 *
 * @internal WordPress action hook
 */
function load_outlet_manager_hook(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen selection.
	if ( 'edit' !== sanitize_key( wp_unslash( $_GET['action'] ?? '' ) ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen selection.
	$id = filter_var( wp_unslash( $_GET['post'] ?? '' ), FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
	if ( false === $id ) {
		return;
	}
	$post = get_post( $id );
	if ( ! $post instanceof \WP_Post ) {
		return;
	}
	try {
		if ( ! uses_outlet_manager( $post ) ) {
			return;
		}
	} catch ( \UnexpectedValueException $e ) {
		wc_get_logger()->error( 'Outlet Page ID could not be retrieved.' );
		return;
	}
	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return;
	}
	if ( ! file_exists( plugin_dir_path( PLUGIN_FILE ) . 'build/outlet-manager.asset.php' ) ) {
		return;
	}
	get_current_screen()->is_block_editor( false );
	add_filter( 'replace_editor', 'OutletPro\\replace_outlet_editor_hook', 10, 2 );
	add_action( 'admin_enqueue_scripts', 'OutletPro\\enqueue_outlet_manager_hook' );
	add_filter( 'admin_body_class', 'OutletPro\\outlet_manager_body_class_hook' );
}

/**
 * Scope the fullscreen editor layout to the manager.
 *
 * Fired by `admin_body_class`.
 *
 * @param string $classes Existing body classes.
 * @internal WordPress filter hook
 */
function outlet_manager_body_class_hook( string $classes ): string {
	return $classes . ' outletpro-manager-screen';
}

/**
 * Render the manager at the page's existing edit URL.
 *
 * Fired by `replace_editor` after the load-post.php registration.
 *
 * @param bool     $replace Whether another plugin replaced the editor.
 * @param \WP_Post $post Page being edited.
 * @internal WordPress filter hook
 */
function replace_outlet_editor_hook( bool $replace, \WP_Post $post ): bool {
	if ( $replace ) {
		return true;
	}
	if ( ! uses_outlet_manager( $post ) ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return false;
	}
	$config = array(
		'pageId'            => $post->ID,
		'canPublish'        => current_user_can( 'publish_pages' ),
		'canManageProducts' => can_manage_outlet_products(),
		'settingsUrl'       => null,
		'categories'        => array(),
		'discountTiers'     => null,
		'priceBands'        => null,
	);
	try {
		$config['discountTiers'] = get_outlet_manager_discount_tiers();
	} catch ( \Throwable $e ) {
		wc_get_logger()->error( 'Outlet manager discount tiers could not be retrieved.' );
	}
	try {
		$config['priceBands'] = get_outlet_manager_price_bands();
	} catch ( \Throwable $e ) {
		wc_get_logger()->error( 'Outlet manager price bands could not be retrieved.' );
	}
	if ( current_user_can( 'edit_theme_options' ) ) {
		$config['settingsUrl'] = wp_is_block_theme()
			? add_query_arg(
				array(
					'postType'           => 'wp_template',
					'postId'             => 'woocommerce/woocommerce//single-product',
					'canvas'             => 'edit',
					'outletpro-settings' => '1',
				),
				admin_url( 'site-editor.php' )
			)
			: add_query_arg( 'autofocus[section]', 'outletpro', admin_url( 'customize.php' ) );
	}
	if ( $config['canManageProducts'] ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) ) {
			wc_get_logger()->error( 'Outlet manager categories could not be retrieved.' );
		} else {
			$config['categories'] = array_map(
				static fn( \WP_Term $term ): array => array(
					'value' => $term->term_id,
					'label' => $term->name,
				),
				$terms
			);
		}
	}
	// Core loads the admin footer when this filter returns true.
	require_once ABSPATH . 'wp-admin/admin-header.php';
	?>
	<div class="wrap">
		<div id="outletpro-outlet-manager" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>"></div>
		<noscript><?php esc_html_e( 'Enable JavaScript to manage your outlet.', 'outletpro' ); ?></noscript>
	</div>
	<?php
	return true;
}

/**
 * Load the dedicated manager bundle without block editor dependencies.
 *
 * Fired by `admin_enqueue_scripts` on the replacement screen.
 *
 * @internal WordPress action hook
 */
function enqueue_outlet_manager_hook(): void {
	$asset = require plugin_dir_path( PLUGIN_FILE ) . 'build/outlet-manager.asset.php';
	wp_enqueue_script( 'outletpro-outlet-manager', plugin_dir_url( PLUGIN_FILE ) . 'build/outlet-manager.js', $asset['dependencies'], $asset['version'], true );
	wp_enqueue_style( 'outletpro-outlet-manager', plugin_dir_url( PLUGIN_FILE ) . 'build/style-outlet-manager.css', array( 'wp-components' ), $asset['version'] );
	wp_set_script_translations( 'outletpro-outlet-manager', 'outletpro' );
}
