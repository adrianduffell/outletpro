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
function init_outlet_editor(): void {
	add_action( 'load-post.php', 'OutletPro\\load_outlet_editor_hook' );
}

/**
 * Determine whether a page uses the archive-based outlet screen.
 *
 * @param \WP_Post $post Candidate page.
 * @internal
 */
function uses_outlet_editor( \WP_Post $post ): bool {
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
 * Register replacement after WordPress's early replace_editor screen probe.
 *
 * Fired by `load-post.php`.
 *
 * @internal WordPress action hook
 */
function load_outlet_editor_hook(): void {
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
		if ( ! uses_outlet_editor( $post ) ) {
			return;
		}
	} catch ( \UnexpectedValueException $e ) {
		wc_get_logger()->error( 'Outlet Page ID could not be retrieved.' );
		return;
	}
	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return;
	}
	if ( ! file_exists( plugin_dir_path( PLUGIN_FILE ) . 'build/outlet-editor.asset.php' ) ) {
		return;
	}
	add_filter( 'replace_editor', 'OutletPro\\replace_outlet_editor_hook', 10, 2 );
}

/**
 * Scope the fullscreen editor layout to the editor.
 *
 * Fired by `admin_body_class`.
 *
 * @param string $classes Existing body classes.
 * @internal WordPress filter hook
 */
function outlet_editor_body_class_hook( string $classes ): string {
	return $classes . ' outletpro-editor-screen';
}

/**
 * Render the editor at the page's existing edit URL.
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
	if ( ! uses_outlet_editor( $post ) ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return false;
	}
	get_current_screen()->is_block_editor( false );
	add_action( 'admin_enqueue_scripts', 'OutletPro\\enqueue_outlet_editor_hook' );
	add_filter( 'admin_body_class', 'OutletPro\\outlet_editor_body_class_hook' );
	$config = array(
		'pageId'     => $post->ID,
		'canPublish' => current_user_can( 'publish_pages' ),
	);
	// Core loads the admin footer when this filter returns true.
	require_once ABSPATH . 'wp-admin/admin-header.php';
	?>
	<div class="wrap">
		<div id="outletpro-outlet-editor" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>"></div>
		<noscript><?php esc_html_e( 'Enable JavaScript to manage your outlet.', 'outletpro' ); ?></noscript>
	</div>
	<?php
	return true;
}

/**
 * Load the dedicated editor bundle without block editor dependencies.
 *
 * Fired by `admin_enqueue_scripts` on the replacement screen.
 *
 * @internal WordPress action hook
 */
function enqueue_outlet_editor_hook(): void {
	$asset = require plugin_dir_path( PLUGIN_FILE ) . 'build/outlet-editor.asset.php';
	wp_enqueue_script( 'outletpro-outlet-editor', plugin_dir_url( PLUGIN_FILE ) . 'build/outlet-editor.js', $asset['dependencies'], $asset['version'], true );
	wp_enqueue_style( 'outletpro-outlet-editor', plugin_dir_url( PLUGIN_FILE ) . 'build/style-outlet-editor.css', array( 'wp-components' ), $asset['version'] );
	wp_enqueue_style( 'outletpro-outlet-editor-components', plugin_dir_url( PLUGIN_FILE ) . 'build/outlet-editor.css', array( 'outletpro-outlet-editor' ), $asset['version'] );
	wp_set_script_translations( 'outletpro-outlet-editor', 'outletpro' );
}
