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
			templateId="theme//archive-product"
			templatePartId="theme//outletpro-outlet-page"
		/>
	);
	try {
		// Act.
		view.rerender(
			<CatalogTemplateLink
				currentUrl={ currentUrl }
				templateId="theme//alternate-outlet"
				templatePartId="theme//outletpro-outlet-page"
			/>
		);

		// Assert.
		const href = view
			.getByRole( 'link', { name: 'Edit Product Catalog' } )
			.getAttribute( 'href' );
		if ( href === null ) {
			throw new Error( 'The template link is missing.' );
		}
		const url = new URL( href );
		const sectionHref = view
			.getByRole( 'link', { name: 'Edit Outlet section' } )
			.getAttribute( 'href' );
		if ( sectionHref === null ) {
			throw new Error( 'The section link is missing.' );
		}
		const sectionUrl = new URL( sectionHref );
		expect( sectionUrl.searchParams.get( 'postType' ) ).toBe(
			'wp_template_part'
		);
		expect( sectionUrl.searchParams.get( 'postId' ) ).toBe(
			'theme//outletpro-outlet-page'
		);
		expect( url.pathname ).toBe( '/store/wp-admin/site-editor.php' );
		expect( url.searchParams.get( 'postId' ) ).toBe(
			'theme//alternate-outlet'
		);
	} finally {
		view.unmount();
	}
} );
