/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

/**
 * Read the Outlet's scoped settings without coercing unexpected values.
 *
 * @param settings Editor settings.
 */
export function getCatalogEditorSettings( settings: object ) {
	return {
		isCatalog:
			'outletproCatalogPage' in settings &&
			settings.outletproCatalogPage === true,
		isBlockTheme:
			'outletproIsBlockTheme' in settings &&
			settings.outletproIsBlockTheme === true,
		supportsTemplateMode:
			'supportsTemplateMode' in settings &&
			settings.supportsTemplateMode === true,
		previewContent:
			'outletproCatalogPreview' in settings &&
			typeof settings.outletproCatalogPreview === 'string'
				? settings.outletproCatalogPreview
				: null,
		templateId:
			'outletproCatalogTemplateId' in settings &&
			typeof settings.outletproCatalogTemplateId === 'string'
				? settings.outletproCatalogTemplateId
				: null,
	};
}

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
