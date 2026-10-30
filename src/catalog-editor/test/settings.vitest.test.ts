/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test } from 'vitest';
import { getCatalogEditorSettings, getCatalogEditorUrl } from '../settings';

test( 'ordinary pages do not enable the catalog preview', () => {
	// Arrange.
	const settings = { supportsTemplateMode: true };

	// Act.
	const result = getCatalogEditorSettings( settings );

	// Assert.
	expect( result.isCatalog ).toBe( false );
	expect( result.templateId ).toBeNull();
} );

test( 'catalog preview settings require booleans without coercion', () => {
	// Arrange.
	const settings = {
		outletproCatalogPage: 'true',
		outletproIsBlockTheme: 1,
		supportsTemplateMode: true,
	};

	// Act.
	const result = getCatalogEditorSettings( settings );

	// Assert.
	expect( result.isCatalog ).toBe( false );
	expect( result.isBlockTheme ).toBe( false );
	expect( result.supportsTemplateMode ).toBe( true );
} );

test( 'catalog preview retains its saved template identity', () => {
	// Arrange.
	const settings = {
		outletproCatalogPage: true,
		outletproIsBlockTheme: true,
		supportsTemplateMode: true,
		outletproCatalogTemplateId: 'purple//outlet-catalog',
	};

	// Act.
	const result = getCatalogEditorSettings( settings );

	// Assert.
	expect( result ).toEqual( {
		isCatalog: true,
		isBlockTheme: true,
		supportsTemplateMode: true,
		templateId: 'purple//outlet-catalog',
		previewContent: null,
	} );
} );

test( 'catalog editing opens the saved template on subdirectory installs', () => {
	// Arrange.
	const currentUrl =
		'https://example.com/store/wp-admin/post.php?post=42&action=edit';

	// Act.
	const url = new URL(
		getCatalogEditorUrl( currentUrl, 'purple//outlet-catalog' )
	);

	// Assert.
	expect( url.pathname ).toBe( '/store/wp-admin/site-editor.php' );
	expect( url.searchParams.get( 'postId' ) ).toBe( 'purple//outlet-catalog' );
	expect( url.searchParams.get( 'postType' ) ).toBe( 'wp_template' );
} );
