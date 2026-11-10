/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

/**
 * Build a link to a catalog template or its Outlet template part.
 *
 * @param currentUrl Current editor URL.
 * @param templateId Shared template identity.
 * @param postType   Template entity type.
 */
export function getCatalogEditorUrl(
	currentUrl: string,
	templateId: string,
	postType: 'wp_template' | 'wp_template_part' = 'wp_template'
): string {
	const url = new URL( 'site-editor.php', currentUrl );
	url.search = new URLSearchParams( {
		postType,
		postId: templateId,
		canvas: 'edit',
	} ).toString();
	return url.href;
}
