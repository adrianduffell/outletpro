/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'edits the outlet page through the styled shell and preserves legacy editing', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	const outlet = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: { title: 'Outlet editor browser test', status: 'draft' },
	} );
	await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/settings',
		data: { outletpro_page_id: outlet.id },
	} );
	try {
		// Act.
		await admin.visitAdminPage(
			'post.php',
			`post=${ outlet.id }&action=edit`
		);
		const sidebar = page.getByRole( 'complementary', {
			name: 'Page settings',
		} );
		const actions = page.getByRole( 'toolbar', { name: 'Page actions' } );
		const review = page.getByRole( 'region', { name: 'Review changes' } );
		const save = actions.getByRole( 'button', { name: /^Save$/ } );
		const applySave = review.getByRole( 'button', { name: /^Save$/ } );
		const slug = sidebar.getByRole( 'textbox', { name: 'Slug' } );
		// Assert.
		await expect( page.locator( '.outletpro-editor-header' ) ).toHaveCSS(
			'display',
			'grid'
		);
		await expect(
			page.locator( '.outletpro-editor-page-details' )
		).toHaveCSS( 'padding-top', '16px' );
		await expect(
			page.getByRole( 'main', { name: 'Outlet workspace' } )
		).toBeEmpty();
		await sidebar.getByRole( 'button', { name: 'Close settings' } ).click();
		await expect(
			actions.getByRole( 'button', { name: 'Page settings' } )
		).toBeFocused();
		await actions.getByRole( 'button', { name: 'Page settings' } ).click();
		await sidebar.getByRole( 'button', { name: 'Rename page' } ).click();
		await page
			.getByRole( 'textbox', { name: 'Name', exact: true } )
			.fill( 'Renamed outlet' );
		await page.getByRole( 'button', { name: 'Done', exact: true } ).click();
		await slug.fill( `editor-${ outlet.id }` );
		await save.click();
		await expect(
			review.getByRole( 'button', { name: 'Cancel' } )
		).toBeFocused();
		await review
			.getByRole( 'checkbox', { name: 'Page details' } )
			.uncheck();
		await expect( applySave ).toBeDisabled();
		await review.getByRole( 'button', { name: 'Cancel' } ).click();
		await expect( slug ).toHaveValue( `editor-${ outlet.id }` );
		await save.click();
		await applySave.click();
		await expect( review ).toHaveCount( 0 );
		await expect(
			sidebar.getByRole( 'heading', { name: 'Renamed outlet' } )
		).toBeVisible();
		await expect( save ).toBeDisabled();
		await actions.getByRole( 'button', { name: /^Publish$/ } ).click();
		await review.getByRole( 'button', { name: /^Publish$/ } ).click();
		await expect(
			sidebar.getByText( 'Published', { exact: true } )
		).toBeVisible();
		const published = await requestUtils.rest( {
			path: `/wp/v2/pages/${ outlet.id }`,
		} );
		await expect(
			actions.getByRole( 'link', { name: 'View outlet' } )
		).toHaveAttribute( 'href', published.link );
		await sidebar.getByRole( 'button', { name: /^Unpublish$/ } ).click();
		await applySave.click();
		await expect(
			sidebar.getByText( 'Draft', { exact: true } )
		).toBeVisible();
		await page.screenshot( {
			path: test.info().outputPath( 'shell-desktop.png' ),
		} );
		await page.setViewportSize( { width: 390, height: 844 } );
		await expect( save ).toBeInViewport();
		await expect( slug ).toBeInViewport();
		await requestUtils.rest( {
			method: 'POST',
			path: `/wp/v2/pages/${ outlet.id }`,
			data: { template: 'outlet-page' },
		} );
		await page.goto( `/wp-admin/post.php?post=${ outlet.id }&action=edit` );
		await expect( page.locator( '#outletpro-outlet-editor' ) ).toHaveCount(
			0
		);
		await expect(
			page.locator( 'iframe[name="editor-canvas"]' )
		).toBeVisible();
	} finally {
		await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/settings',
			data: { outletpro_page_id: settings.outletpro_page_id },
		} );
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wp/v2/pages/${ outlet.id }`,
			params: { force: true },
		} );
	}
} );

test( 'locks editing after the first failed page update', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	const outlet = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: { title: 'Failing outlet editor', status: 'draft' },
	} );
	await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/settings',
		data: { outletpro_page_id: outlet.id },
	} );
	let writes = 0;
	await page.route(
		( url ) =>
			( url.searchParams.get( 'rest_route' ) ?? url.pathname ).endsWith(
				`/wp/v2/pages/${ outlet.id }`
			),
		async ( route ) => {
			if ( ! [ 'POST', 'PUT' ].includes( route.request().method() ) ) {
				return route.continue();
			}
			writes++;
			await route.fulfill( {
				status: 500,
				json: { code: 'editor_test', message: 'Page update failed.' },
			} );
		}
	);
	try {
		// Act.
		await admin.visitAdminPage(
			'post.php',
			`post=${ outlet.id }&action=edit`
		);
		const slug = page.getByRole( 'textbox', { name: 'Slug' } );
		await slug.fill( 'failed-slug' );
		const actions = page.getByRole( 'toolbar', { name: 'Page actions' } );
		await actions.getByRole( 'button', { name: /^Save$/ } ).click();
		const review = page.getByRole( 'region', { name: 'Review changes' } );
		await review.getByRole( 'button', { name: /^Save$/ } ).click();
		// Assert.
		await expect(
			page.getByText(
				'Changes could not be saved. Reload the editor before continuing.',
				{ exact: true }
			)
		).toBeVisible();
		await expect(
			review.getByRole( 'button', { name: 'Save', exact: true } )
		).toBeDisabled();
		await expect( review.getByRole( 'checkbox' ) ).toBeDisabled();
		await review.getByRole( 'button', { name: 'Cancel' } ).click();
		await expect( slug ).toBeDisabled();
		await expect( slug ).toHaveValue( 'failed-slug' );
		expect( writes ).toBe( 1 );
	} finally {
		await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/settings',
			data: { outletpro_page_id: settings.outletpro_page_id },
		} );
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wp/v2/pages/${ outlet.id }`,
			params: { force: true },
		} );
	}
} );
