/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test } from 'vitest';
import { getCatalogEditorUrl } from '../settings';

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
