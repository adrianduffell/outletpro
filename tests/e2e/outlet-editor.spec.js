/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

async function visitOutletEditor( admin, requestUtils ) {
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	await admin.visitAdminPage(
		'post.php',
		`post=${ settings.outletpro_page_id }&action=edit`
	);
}

test.beforeAll( async ( { requestUtils } ) => {
	await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/settings',
		data: { outletpro_markdown_levels: [ 30, 50, 70 ] },
	} );
} );

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
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	const save = actions.getByRole( 'button', { name: /^Save$/ } );
	const applySave = review.getByRole( 'button', { name: /^Save$/ } );
	const slugButton = sidebar.getByRole( 'button', { name: 'Edit slug' } );
	const slugPopover = page.getByRole( 'dialog', {
		name: 'Slug',
		exact: true,
	} );
	const slug = slugPopover.getByRole( 'textbox', { name: 'Slug' } );
	// Assert.
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
	await page
		.getByRole( 'textbox', { name: 'Name', exact: true } )
		.press( 'Enter' );
	await expect(
		sidebar.getByRole( 'button', { name: 'Rename page' } )
	).toBeFocused();
	await slugButton.click();
	await expect( slug ).toBeFocused();
	await slug.fill( `editor-${ outlet.id }` );
	await slug.press( 'Escape' );
	await expect( slugButton ).toBeFocused();
	await expect( sidebar ).toBeVisible();
	await save.click();
	await expect(
		review.getByRole( 'button', { name: 'Cancel' } )
	).toBeFocused();
	await page.screenshot( {
		path: test.info().outputPath( 'review-desktop.png' ),
	} );
	await review.getByRole( 'checkbox', { name: 'Renamed outlet' } ).uncheck();
	await expect( applySave ).toBeDisabled();
	await review.getByRole( 'button', { name: 'Cancel' } ).click();
	await expect( save ).toBeFocused();
	await expect( slugButton ).toHaveText( `editor-${ outlet.id }` );
	await save.click();
	await applySave.click();
	await expect( review ).toHaveCount( 0 );
	await expect(
		page.getByText( 'Site updated.', { exact: true } )
	).toBeVisible();
	await expect(
		sidebar.getByRole( 'heading', { name: 'Renamed outlet' } )
	).toBeVisible();
	await expect( save ).toBeDisabled();
	await expect(
		actions.getByRole( 'button', { name: 'Page settings' } )
	).toBeFocused();
	await actions.getByRole( 'button', { name: /^Publish$/ } ).click();
	await review.getByRole( 'button', { name: /^Save$/ } ).click();
	await expect( save ).not.toHaveClass( /is-busy/ );
	await expect( save ).toBeDisabled();
	await expect(
		page.getByText( 'Site updated.', { exact: true } )
	).toBeVisible();
	await expect(
		sidebar.getByText( 'Published', { exact: true } )
	).toBeVisible();
	const published = await requestUtils.rest( {
		path: `/wp/v2/pages/${ outlet.id }`,
	} );
	await expect(
		actions.getByRole( 'link', { name: 'View outlet' } )
	).toHaveAttribute( 'href', published.link );
	await sidebar.getByRole( 'button', { name: 'Change status' } ).click();
	const statusPopover = page.getByRole( 'dialog', {
		name: 'Status',
		exact: true,
	} );
	const draft = statusPopover.getByRole( 'radio', {
		name: 'Draft',
		exact: true,
	} );
	await draft.check();
	await expect( review ).toHaveCount( 0 );
	await draft.press( 'Escape' );
	await saveChanges( page );
	await expect( sidebar.getByText( 'Draft', { exact: true } ) ).toBeVisible();
	await page.screenshot( {
		path: test.info().outputPath( 'shell-desktop.png' ),
	} );
	await page.getByRole( 'button', { name: 'Dismiss this notice' } ).click();
	await page.setViewportSize( { width: 390, height: 844 } );
	await expect( save ).toBeInViewport();
	await expect( slugButton ).toBeInViewport();
	await sidebar.getByRole( 'button', { name: 'Change status' } ).click();
	await expect( statusPopover ).toBeInViewport();
	await expect(
		statusPopover.getByRole( 'radio', { name: 'Published', exact: true } )
	).toBeInViewport();
	await statusPopover.getByRole( 'button', { name: 'Close' } ).click();
	await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/settings',
		data: { outletpro_page_id: settings.outletpro_page_id },
	} );
	await requestUtils.rest( {
		method: 'DELETE',
		path: `/wp/v2/pages/${ outlet.id }?force=true`,
	} );
} );

async function saveChanges( page ) {
	const actions = page.getByRole( 'toolbar', { name: 'Page actions' } );
	await actions.getByRole( 'button', { name: /^Save$/ } ).click();
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	await review.getByRole( 'button', { name: /^Save$/ } ).click();
	await expect( review ).toHaveCount( 0 );
	await expect(
		page.getByText( 'Site updated.', { exact: true } )
	).toBeVisible();
	await expect(
		actions.getByRole( 'button', { name: /^Save$/ } )
	).not.toHaveClass( /is-busy/ );
	await expect(
		actions.getByRole( 'button', { name: /^Save$/ } )
	).toBeDisabled();
}

test( 'views markdown levels', async ( { page, admin, requestUtils } ) => {
	// Act.
	await visitOutletEditor( admin, requestUtils );
	// Assert.
	await expect(
		page.getByRole( 'button', { name: 'Add markdown level' } )
	).toBeVisible();
	await expect(
		page.getByRole( 'button', { name: /^\d+% off$/ } )
	).toHaveText( [ '30% off', '50% off', '70% off' ] );
} );

test( 'adds a markdown level', async ( { page, admin, requestUtils } ) => {
	// Arrange.
	await visitOutletEditor( admin, requestUtils );
	const levels = page.getByRole( 'button', { name: /^\d+% off$/ } );
	await expect( levels.first() ).toBeVisible();
	const initialLevels = await levels.allTextContents();
	// Act.
	await page.getByRole( 'button', { name: 'Add markdown level' } ).click();
	await page.getByRole( 'spinbutton', { name: 'Percentage' } ).fill( '40' );
	await page.getByRole( 'button', { name: 'Add', exact: true } ).click();
	await saveChanges( page );
	await page.reload();
	// Assert.
	await expect(
		page.getByRole( 'button', { name: /^40% off$/ } )
	).toBeVisible();
	await expect( levels ).toHaveCount( initialLevels.length + 1 );
	for ( const level of initialLevels ) {
		await expect(
			page.getByRole( 'button', { name: level, exact: true } )
		).toBeVisible();
	}
} );

test( 'edits a markdown level', async ( { page, admin, requestUtils } ) => {
	// Arrange.
	await visitOutletEditor( admin, requestUtils );
	const levels = page.getByRole( 'button', { name: /^\d+% off$/ } );
	await expect( levels.first() ).toBeVisible();
	const initialLevels = await levels.allTextContents();
	// Act.
	await page.getByRole( 'button', { name: /^30% off$/ } ).press( 'Enter' );
	const percentage = page.getByRole( 'spinbutton', { name: 'Percentage' } );
	await expect( percentage ).toBeFocused();
	await percentage.fill( '60' );
	await percentage.press( 'Enter' );
	await expect(
		page.getByRole( 'button', { name: /^60% off$/ } )
	).toBeFocused();
	await saveChanges( page );
	await page.reload();
	// Assert.
	await expect(
		page.getByRole( 'button', { name: /^60% off$/ } )
	).toBeVisible();
	await expect(
		page.getByRole( 'button', { name: /^30% off$/ } )
	).toHaveCount( 0 );
	await expect( levels ).toHaveCount( initialLevels.length );
	for ( const level of initialLevels.filter(
		( label ) => label !== '30% off'
	) ) {
		await expect(
			page.getByRole( 'button', { name: level, exact: true } )
		).toBeVisible();
	}
} );

test( 'deletes a markdown level', async ( { page, admin, requestUtils } ) => {
	// Arrange.
	await visitOutletEditor( admin, requestUtils );
	const levels = page.getByRole( 'button', { name: /^\d+% off$/ } );
	await expect( levels.first() ).toBeVisible();
	const initialLevels = await levels.allTextContents();
	// Act.
	await page.getByRole( 'button', { name: /^70% off$/ } ).hover();
	await page
		.getByRole( 'button', { name: 'Options', exact: true } )
		.last()
		.click();
	await page.getByRole( 'menuitem', { name: 'Remove 70% off' } ).click();
	await saveChanges( page );
	await page.reload();
	// Assert.
	await expect(
		page.getByRole( 'button', { name: 'Add markdown level' } )
	).toBeVisible();
	await expect(
		page.getByRole( 'button', { name: /^70% off$/ } )
	).toHaveCount( 0 );
	await expect( levels ).toHaveCount( initialLevels.length - 1 );
	for ( const level of initialLevels.filter(
		( label ) => label !== '70% off'
	) ) {
		await expect(
			page.getByRole( 'button', { name: level, exact: true } )
		).toBeVisible();
	}
} );
