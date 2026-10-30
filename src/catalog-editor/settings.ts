/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

/**
 * Build a link to the saved Outlet catalog template.
 *
 * @param currentUrl Current editor URL.
 * @param templateId Shared template identity.
 */
export function getCatalogEditorUrl(
	currentUrl: string,
	templateId: string
): string {
	const url = new URL( 'site-editor.php', currentUrl );
	url.search = new URLSearchParams( {
		postType: 'wp_template',
		postId: templateId,
		canvas: 'edit',
	} ).toString();
	return url.href;
}
