/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'edits the outlet page through the styled shell', async ( {
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
	// Act.
	await admin.visitAdminPage( 'post.php', `post=${ outlet.id }&action=edit` );
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
	await expect( page.locator( '.outletpro-editor-page-details' ) ).toHaveCSS(
		'padding-top',
		'16px'
	);
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
	await review.getByRole( 'checkbox', { name: 'Page details' } ).uncheck();
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
	await expect( sidebar.getByText( 'Draft', { exact: true } ) ).toBeVisible();
	await page.screenshot( {
		path: test.info().outputPath( 'shell-desktop.png' ),
	} );
	await page.setViewportSize( { width: 390, height: 844 } );
	await expect( save ).toBeInViewport();
	await expect( slug ).toBeInViewport();

	await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/settings',
		data: { outletpro_page_id: settings.outletpro_page_id },
	} );
} );
