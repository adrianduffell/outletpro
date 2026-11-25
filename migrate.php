<?php
/**
 * Database migrations.
 *
 * Migrations are run each time database-backed config items are added or removed.
 * This includes settings, theme mods, and taxonomies. All migrations should be
 * backwards compatible to allow the plugin to be rolled back to an earlier version.
 *
 * @package OutletPro
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

namespace OutletPro;

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( version_compare( get_option( 'outletpro_db_version', '1' ), DB_VERSION, '>=' ) ) {
	return;
}

if ( version_compare( get_option( 'outletpro_db_version', '1' ), '2', '<' ) ) {
	if ( wp_is_block_theme() ) {
		set_theme_mod( SINGLE_PRODUCT_BADGE_BLOCK_HOOK_THEME_MOD, array( 'core/post-title', 'before' ) );
	}
	if ( wp_is_block_theme() ) {
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_HOOK_THEME_MOD, array( 'woocommerce/product-meta', 'first_child' ) );
	}
	if ( ! wp_is_block_theme() ) {
		set_theme_mod( SINGLE_PRODUCT_BADGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_single_product_summary', 1 ) );
	}
	if ( ! wp_is_block_theme() ) {
		set_theme_mod( SINGLE_PRODUCT_MESSAGE_TEMPLATE_HOOK_THEME_MOD, array( 'woocommerce_product_meta_start', 1 ) );
	}
}

if ( version_compare( get_option( 'outletpro_db_version', '1' ), '3', '<' ) ) {
	set_theme_mod( SINGLE_PRODUCT_MESSAGE_SHOW_LINK_THEME_MOD, false );
	set_theme_mod( SINGLE_PRODUCT_MESSAGE_BOLD_THEME_MOD, true );
	set_theme_mod( SINGLE_PRODUCT_MESSAGE_BLOCK_STYLE_THEME_MOD, 'fine' );
	add_option( LEGACY_OUTLET_PAGE_TEMPLATE_OPTION, 'yes' );
	( static function () use ( $wpdb ): void {
		$price_levels = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				'SELECT DISTINCT CAST(SUBSTRING(term.slug, 14) AS UNSIGNED) AS price_level FROM %i AS term INNER JOIN %i AS taxonomy ON taxonomy.term_id = term.term_id WHERE taxonomy.taxonomy = %s AND term.slug REGEXP %s ORDER BY price_level',
				$wpdb->terms,
				$wpdb->term_taxonomy,
				'outletpro_search_index',
				'^outlet-price-[0-9]+$'
			)
		);
		add_option( OUTLET_PRICE_LEVELS_OPTION, '' === $wpdb->last_error ? array_map( 'absint', $price_levels ) : array() );
	} )();
	add_option( OUTLET_MARKDOWN_LEVELS_OPTION, array( 30, 50, 70 ) );
}

update_option( 'outletpro_db_version', DB_VERSION, true );
