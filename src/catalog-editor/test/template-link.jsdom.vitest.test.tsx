/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { render } from '@testing-library/react';
import { expect, test } from 'vitest';
import { CatalogTemplateLink } from '../template-link';

test( 'the sidebar link follows a newly selected template', () => {
	// Arrange.
	const currentUrl =
		'https://example.com/store/wp-admin/post.php?post=42&action=edit';
	const view = render(
		<CatalogTemplateLink
			currentUrl={ currentUrl }
			templateId="theme//outlet-catalog"
		/>
	);
	try {
		// Act.
		view.rerender(
			<CatalogTemplateLink
				currentUrl={ currentUrl }
				templateId="theme//alternate-outlet"
			/>
		);

		// Assert.
		const href = view
			.getByRole( 'link', { name: 'Edit outlet template' } )
			.getAttribute( 'href' );
		if ( href === null ) {
			throw new Error( 'The template link is missing.' );
		}
		const url = new URL( href );
		expect( url.pathname ).toBe( '/store/wp-admin/site-editor.php' );
		expect( url.searchParams.get( 'postId' ) ).toBe(
			'theme//alternate-outlet'
		);
	} finally {
		view.unmount();
	}
} );
