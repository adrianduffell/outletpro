/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

async function visitOutletEditor( admin, requestUtils ) {
	await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/settings',
		data: {
			outletpro_markdown_levels: [ 30, 50, 70 ],
			outletpro_price_levels: [ 10, 25, 50 ],
		},
	} );
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	await admin.visitAdminPage(
		'post.php',
		`post=${ settings.outletpro_page_id }&action=edit`
	);
}

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
	const slug = sidebar.getByRole( 'textbox', { name: 'Slug' } );
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
	await page.getByRole( 'button', { name: 'Done', exact: true } ).click();
	await slug.fill( `editor-${ outlet.id }` );
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
	await expect( slug ).toHaveValue( `editor-${ outlet.id }` );
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
}

test( 'edits and saves global badge settings from the Badge tab', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	await visitOutletEditor( admin, requestUtils );
	try {
		// Act.
		await page.getByRole( 'tab', { name: 'Page' } ).press( 'ArrowRight' );
		const badge = page.getByRole( 'tabpanel', { name: 'Badge' } );
		await badge.getByRole( 'button', { name: /Change design:/ } ).click();

		// Assert.
		const presets = page.getByRole( 'group', { name: 'Presets' } );
		await expect( presets ).toHaveCSS( 'display', 'grid' );
		await expect(
			page.locator(
				'.outletpro-badge-presets-popover .components-popover__content'
			)
		).toHaveCSS( 'width', '400px' );
		await page.screenshot( {
			path: test.info().outputPath( 'badge-design-desktop.png' ),
		} );

		// Act.
		await page
			.getByRole( 'button', { name: 'Clearance', exact: true } )
			.click();
		await badge
			.getByRole( 'textbox', { name: 'Label' } )
			.fill( 'Clearance specials' );
		await page.getByRole( 'tab', { name: 'Page' } ).click();
		await expect(
			page.getByRole( 'button', { name: '30% off' } )
		).toBeVisible();
		await page.getByRole( 'tab', { name: 'Badge' } ).click();
		await saveChanges( page );
		await page.reload();
		await page.getByRole( 'tab', { name: 'Badge' } ).click();

		// Assert.
		await expect(
			badge.getByRole( 'textbox', { name: 'Label' } )
		).toHaveValue( 'Clearance specials' );
		await expect(
			badge.getByText( /Changes apply to the whole site/ )
		).toBeVisible();
		const savedSettings = await requestUtils.rest( {
			path: '/wp/v2/settings',
		} );
		expect( savedSettings.outletpro_badge_label ).toBe(
			'Clearance specials'
		);
		expect( savedSettings.outletpro_badge_text_color ).toBe( '#222' );
		expect( savedSettings.outletpro_badge_bg_color ).toBe( '#FFEE85' );
		await page.setViewportSize( { width: 390, height: 844 } );
		await expect(
			badge.getByRole( 'textbox', { name: 'Label' } )
		).toBeInViewport();
		await badge.getByRole( 'button', { name: /Change design:/ } ).click();
		await expect( presets ).toHaveCSS( 'display', 'grid' );
		await page.screenshot( {
			path: test.info().outputPath( 'badge-design-mobile.png' ),
		} );
	} finally {
		await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/settings',
			data: {
				outletpro_badge_label: settings.outletpro_badge_label,
				outletpro_badge_text_color: settings.outletpro_badge_text_color,
				outletpro_badge_bg_color: settings.outletpro_badge_bg_color,
			},
		} );
	}
} );

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
		.getByRole( 'region', { name: 'Markdown Levels' } )
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

test( 'adds, edits, and removes price levels through native saving', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	await visitOutletEditor( admin, requestUtils );
	const prices = page.getByRole( 'region', { name: 'Price Levels' } );
	const levels = prices.getByRole( 'button', { name: /^Under / } );
	// Act.
	await prices.getByRole( 'button', { name: 'Add price level' } ).click();
	await page.getByRole( 'spinbutton', { name: 'Price' } ).fill( '1250' );
	await page.getByRole( 'button', { name: 'Add', exact: true } ).click();
	await saveChanges( page );
	await page.reload();
	await expect( levels ).toHaveCount( 4 );
	await levels.last().click();
	await page.getByRole( 'spinbutton', { name: 'Price' } ).fill( '1500' );
	await page.getByRole( 'spinbutton', { name: 'Price' } ).press( 'Enter' );
	await expect( levels.last() ).toBeFocused();
	await saveChanges( page );
	await page.reload();
	await levels.last().hover();
	await prices
		.getByRole( 'button', { name: 'Options', exact: true } )
		.last()
		.click();
	await page.getByRole( 'menuitem', { name: /^Remove Under / } ).click();
	await saveChanges( page );
	await page.reload();
	// Assert.
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	expect( settings.outletpro_price_levels ).toEqual( [ 10, 25, 50 ] );
	expect( settings.outletpro_markdown_levels ).toEqual( [ 30, 50, 70 ] );
	await expect( levels ).toHaveCount( 3 );
} );
