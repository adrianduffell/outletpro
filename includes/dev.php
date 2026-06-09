<?php
/**
 * Dev build splash screen.
 *
 * This file is intended for development builds only and should be stripped
 * from production releases.
 *
 * @package OutletPro
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

/**
 * Initialize dev build features.
 *
 * @internal
 */
function init_dev(): void {
	add_action( 'admin_bar_menu', 'OutletPro\add_dev_admin_bar_node_hook', 100 );
	add_action( 'admin_footer', 'OutletPro\output_dev_splash_hook' );
	add_action( 'admin_enqueue_scripts', 'OutletPro\enqueue_dev_assets_hook' );
}

/**
 * Add the Outlet Pro toolbar item to re-open the splash screen.
 *
 * Fired by `admin_bar_menu`.
 *
 * @param \WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
 * @internal WordPress action hook
 */
function add_dev_admin_bar_node_hook( \WP_Admin_Bar $wp_admin_bar ): void {
	$wp_admin_bar->add_node(
		array(
			'id'    => 'outletpro-dev',
			'title' => 'Outlet Pro',
			'href'  => '#',
		)
	);
}

/**
 * Output the dev splash screen HTML.
 *
 * Fired by `admin_footer`.
 *
 * @internal WordPress action hook
 */
function output_dev_splash_hook(): void {
	?>
	<div id="outletpro-dev-splash" class="outletpro-dev-splash" hidden>
		<div class="outletpro-dev-splash__content">
			<h1 class="outletpro-dev-splash__title"><?php esc_html_e( 'Welcome to the Outlet Pro dev build', 'outletpro' ); ?></h1>
			<p><?php esc_html_e( 'Thanks for installing the dev build. Help shape Outlet Pro by reporting a bug or requesting a new feature on GitHub.', 'outletpro' ); ?></p>
			<p>&mdash; Adrian</p>
			<div class="outletpro-dev-splash__disclaimer">
				<svg class="outletpro-dev-splash__warning-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
					<path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/>
				</svg>
				<p><?php esc_html_e( 'Do not use on a live store. This build contains work in progress and is intended for development purposes only.', 'outletpro' ); ?></p>
			</div>
			<div class="outletpro-dev-splash__footer">
				<div class="outletpro-dev-splash__actions">
					<a href="https://github.com/adrianduffell/outletpro/" target="_blank" rel="noopener noreferrer" class="button outletpro-dev-splash__github-button">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
							<path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
						</svg>
						<?php esc_html_e( 'GitHub', 'outletpro' ); ?>
					</a>
					<button type="button" id="outletpro-dev-splash-dismiss" class="button button-primary">
						<?php esc_html_e( 'Get started', 'outletpro' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Enqueue scripts and styles for the dev splash screen.
 *
 * Fired by `admin_enqueue_scripts`.
 *
 * @internal WordPress action hook
 */
function enqueue_dev_assets_hook(): void {
	wp_enqueue_style(
		'outletpro-dev',
		plugin_dir_url( PLUGIN_FILE ) . 'assets/css/dev.css',
		array(),
		VERSION
	);

	wp_enqueue_script(
		'outletpro-dev',
		plugin_dir_url( PLUGIN_FILE ) . 'assets/js/dev.js',
		array(),
		VERSION,
		true
	);
}
