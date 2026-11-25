/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test as base, expect } from '@wordpress/e2e-test-utils-playwright';

const test = base.extend( {
	sharedPage: [
		async ( { browser, requestUtils }, providePage ) => {
			const settings = await requestUtils.rest( {
				path: '/wp/v2/settings',
			} );
			const outlet = await requestUtils.rest( {
				method: 'POST',
				path: '/wp/v2/pages',
				data: { title: 'Outlet editor browser test', status: 'draft' },
			} );
			const { create: products } = await requestUtils.rest( {
				method: 'POST',
				path: '/wc/v3/products/batch',
				data: {
					create: Array.from( { length: 30 }, ( _, index ) => ( {
						name: `Outlet editor product ${ ( index + 1 ).toString().padStart( 2, '0' ) }`,
						sku: `OUTLET-${ outlet.id }-${ index + 1 }`,
						type: 'simple',
						status: 'publish',
						regular_price: '20',
						sale_price: index === 0 ? '10' : '',
						outletpro: { is_outlet: index === 0 },
					} ) ),
				},
			} );
			for ( const product of products ) {
				if ( ! Number.isInteger( product.id ) ) {
					throw new Error( 'Product fixture could not be created.' );
				}
				if ( product.id < 1 ) {
					throw new Error( 'Product fixture could not be created.' );
				}
			}
			await requestUtils.rest( {
				method: 'POST',
				path: '/wp/v2/settings',
				data: {
					outletpro_page_id: outlet.id,
					outletpro_markdown_levels: [ 30, 50, 70 ],
					outletpro_price_levels: [ 10, 25, 50 ],
				},
			} );
			const page = await browser.newPage( {
				baseURL: requestUtils.baseURL,
				storageState: requestUtils.storageState,
				viewport: { width: 1280, height: 900 },
				reducedMotion: 'reduce',
			} );
			await page.goto(
				`wp-admin/post.php?post=${ outlet.id }&action=edit`
			);
			await page
				.getByRole( 'button', { name: 'Close settings' } )
				.click();
			await providePage( page );
			await page.close();
			await requestUtils.rest( {
				method: 'POST',
				path: '/wp/v2/settings',
				data: {
					outletpro_page_id: settings.outletpro_page_id,
					outletpro_markdown_levels:
						settings.outletpro_markdown_levels,
					outletpro_price_levels: settings.outletpro_price_levels,
				},
			} );
			await requestUtils.rest( {
				method: 'POST',
				path: '/wc/v3/products/batch',
				data: { delete: products.map( ( product ) => product.id ) },
			} );
			await requestUtils.rest( {
				method: 'DELETE',
				path: `/wp/v2/pages/${ outlet.id }`,
				params: { force: true },
			} );
		},
		{ scope: 'worker' },
	],
	page: async ( { sharedPage }, providePage ) => {
		await providePage( sharedPage );
	},
} );

test.describe.configure( { mode: 'serial' } );

test( 'views the products', async ( { page } ) => {
	// Act.
	await page
		.getByRole( 'searchbox', { name: 'Search products by name or SKU' } )
		.fill( 'Outlet editor product' );
	// Assert.
	await expect( page.getByRole( 'row' ) ).toHaveCount( 26 );
	await expect(
		page.getByRole( 'link', {
			name: 'Outlet editor product 01',
			exact: true,
		} )
	).toBeVisible();
	await expect(
		page
			.getByRole( 'toolbar', { name: 'Page actions' } )
			.getByRole( 'button', { name: 'Save', exact: true } )
	).toBeDisabled();
} );

test( 'displays outlet products only', async ( { page } ) => {
	// Act.
	await page
		.getByRole( 'checkbox', { name: 'Show outlet products only' } )
		.check();
	// Assert.
	await expect( page.getByRole( 'row' ) ).toHaveCount( 2 );
	await expect(
		page.getByRole( 'link', {
			name: 'Outlet editor product 01',
			exact: true,
		} )
	).toBeVisible();
	await expect(
		page.getByRole( 'link', {
			name: 'Outlet editor product 02',
			exact: true,
		} )
	).toHaveCount( 0 );
} );

test( 'searches for a product', async ( { page } ) => {
	// Arrange.
	const search = page.getByRole( 'searchbox', {
		name: 'Search products by name or SKU',
	} );
	const sku = await page
		.getByRole( 'row', { name: /Outlet editor product 01/ } )
		.getByText( /^OUTLET-/ )
		.innerText();
	await page
		.getByRole( 'checkbox', { name: 'Show outlet products only' } )
		.uncheck();
	// Act.
	await search.fill( 'Outlet editor product 30' );
	// Assert.
	await expect( page.getByRole( 'row' ) ).toHaveCount( 2 );
	await expect(
		page.getByRole( 'link', {
			name: 'Outlet editor product 30',
			exact: true,
		} )
	).toBeVisible();
	// Act.
	await search.fill( sku );
	// Assert.
	await expect( page.getByRole( 'row' ) ).toHaveCount( 2 );
	await expect(
		page.getByRole( 'link', {
			name: 'Outlet editor product 01',
			exact: true,
		} )
	).toBeVisible();
} );

test( 'includes an outlet product', async ( { page } ) => {
	// Arrange.
	await page
		.getByRole( 'searchbox', { name: 'Search products by name or SKU' } )
		.fill( 'Outlet editor product 02' );
	const toggle = page.getByRole( 'checkbox', {
		name: 'Include Outlet editor product 02 in outlet',
		exact: true,
	} );
	await expect( toggle ).not.toBeChecked();
	// Act.
	await toggle.check();
	// Assert.
	await expect( toggle ).toBeChecked();
	await expect(
		page
			.getByRole( 'toolbar', { name: 'Page actions' } )
			.getByRole( 'button', { name: 'Save', exact: true } )
	).toBeEnabled();
} );

test( 'removes an outlet product', async ( { page } ) => {
	// Arrange.
	await page
		.getByRole( 'searchbox', { name: 'Search products by name or SKU' } )
		.fill( 'Outlet editor product 01' );
	const toggle = page.getByRole( 'checkbox', {
		name: 'Include Outlet editor product 01 in outlet',
		exact: true,
	} );
	await expect( toggle ).toBeChecked();
	// Act.
	await toggle.uncheck();
	// Assert.
	await expect( toggle ).not.toBeChecked();
	await expect(
		page
			.getByRole( 'toolbar', { name: 'Page actions' } )
			.getByRole( 'button', { name: 'Save', exact: true } )
	).toBeEnabled();
} );

test( 'renames the outlet page', async ( { page } ) => {
	// Arrange.
	const sidebar = page.getByRole( 'complementary', {
		name: 'Page settings',
	} );
	await page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Page settings' } )
		.click();
	// Act.
	await sidebar.getByRole( 'button', { name: 'Rename page' } ).click();
	await page
		.getByRole( 'textbox', { name: 'Name', exact: true } )
		.fill( 'Renamed outlet' );
	await page
		.getByRole( 'textbox', { name: 'Name', exact: true } )
		.press( 'Enter' );
	// Assert.
	await expect(
		sidebar.getByRole( 'heading', { name: 'Renamed outlet' } )
	).toBeVisible();
} );

test( 'changes the outlet slug', async ( { page } ) => {
	// Arrange.
	const slug = `editor-${ new URL( page.url() ).searchParams.get( 'post' ) }`;
	// Act.
	await page.getByRole( 'button', { name: 'Not set', exact: true } ).click();
	await page.getByRole( 'textbox', { name: 'Slug' } ).fill( slug );
	await page.getByRole( 'textbox', { name: 'Slug' } ).press( 'Enter' );
	// Assert.
	await expect(
		page
			.getByRole( 'toolbar', { name: 'Page actions' } )
			.getByRole( 'button', { name: 'Save', exact: true } )
	).toBeEnabled();
} );

test( 'cancels saving without losing changes', async ( { page } ) => {
	// Arrange.
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	const slug = `editor-${ new URL( page.url() ).searchParams.get( 'post' ) }`;
	// Act.
	for ( const width of [ 1280, 390 ] ) {
		await page.setViewportSize( { width, height: 900 } );
		await save.click();
		// Assert.
		await expect( review ).toBeVisible();
		for ( const name of [ 'Save', 'Publish' ] ) {
			const action = page
				.getByRole( 'toolbar', { name: 'Page actions' } )
				.getByRole( 'button', { name, exact: true } );
			await expect
				.poll( () =>
					action.evaluate( ( button ) => {
						const {
							x,
							y,
							width: w,
							height,
						} = button.getBoundingClientRect();
						return document
							.elementFromPoint( x + w / 2, y + height / 2 )
							?.closest( '#outletpro-editor-sidebar' )
							?.getAttribute( 'aria-label' );
					} )
				)
				.toBe( 'Review changes' );
		}
		await page.screenshot( {
			path: test.info().outputPath( `review-${ width }.png` ),
		} );
		// Act.
		await review.getByRole( 'button', { name: 'Cancel' } ).click();
		// Assert.
		await expect( save ).toBeFocused();
	}
	await page.setViewportSize( { width: 1280, height: 900 } );
	// Assert.
	await expect( save ).toBeFocused();
	await expect(
		page.getByRole( 'button', { name: slug, exact: true } )
	).toHaveText( slug );
	await expect(
		page.getByRole( 'checkbox', {
			name: 'Include Outlet editor product 01 in outlet',
			exact: true,
		} )
	).not.toBeChecked();
} );

test( 'saves selected changes', async ( { page } ) => {
	// Arrange.
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	const slug = `editor-${ new URL( page.url() ).searchParams.get( 'post' ) }`;
	// Act.
	await save.click();
	await review
		.getByRole( 'checkbox', {
			name: 'Outlet editor product 02',
			exact: true,
		} )
		.uncheck();
	await review
		.getByRole( 'checkbox', { name: 'Renamed outlet', exact: true } )
		.uncheck();
	await review.getByRole( 'button', { name: 'Save', exact: true } ).click();
	await expect(
		page.getByText( 'Site updated.', { exact: true } )
	).toBeVisible();
	await page
		.getByRole( 'searchbox', { name: 'Search products by name or SKU' } )
		.fill( 'Outlet editor product' );
	await page
		.getByRole( 'checkbox', { name: 'Show outlet products only' } )
		.check();
	// Assert.
	await expect(
		page.getByText( 'No products found', { exact: true } )
	).toBeVisible();
	await expect(
		page.getByRole( 'button', { name: slug, exact: true } )
	).toHaveText( slug );
	await expect( save ).toBeEnabled();
} );

test( 'saves', async ( { page } ) => {
	// Arrange.
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	// Act.
	await save.click();
	await expect(
		review.getByRole( 'checkbox', {
			name: 'Outlet editor product 02',
			exact: true,
		} )
	).toBeChecked();
	await expect(
		review.getByRole( 'checkbox', { name: 'Renamed outlet', exact: true } )
	).toBeChecked();
	await review.getByRole( 'button', { name: 'Save', exact: true } ).click();
	// Assert.
	await expect(
		page.getByText( 'Site updated.', { exact: true } )
	).toBeVisible();
	await expect( save ).toBeDisabled();
	await expect( page.getByRole( 'row' ) ).toHaveCount( 2 );
	await expect(
		page.getByRole( 'link', {
			name: 'Outlet editor product 02',
			exact: true,
		} )
	).toBeVisible();
	await expect(
		page.getByRole( 'checkbox', {
			name: 'Include Outlet editor product 02 in outlet',
			exact: true,
		} )
	).toBeChecked();
} );

test( 'views another page of products', async ( { page } ) => {
	// Arrange.
	await page
		.getByRole( 'checkbox', { name: 'Show outlet products only' } )
		.uncheck();
	await expect( page.getByRole( 'row' ) ).toHaveCount( 26 );
	// Act.
	await page.getByRole( 'button', { name: 'Next page' } ).click();
	// Assert.
	await expect( page.getByRole( 'row' ) ).toHaveCount( 6 );
	await expect(
		page.getByRole( 'link', {
			name: 'Outlet editor product 30',
			exact: true,
		} )
	).toBeVisible();
} );

test( 'changes the number of products shown', async ( { page } ) => {
	// Act.
	await page.getByRole( 'button', { name: 'View options' } ).click();
	await page.getByRole( 'radio', { name: '50', exact: true } ).click();
	// Assert.
	await expect( page.getByRole( 'row' ) ).toHaveCount( 31 );
	// Act.
	await page.getByRole( 'radio', { name: '10', exact: true } ).click();
	await page.keyboard.press( 'Escape' );
	// Assert.
	await expect( page.getByRole( 'row' ) ).toHaveCount( 11 );
} );

test( 'sorts products by name', async ( { page } ) => {
	// Act.
	await page.getByRole( 'button', { name: 'View options' } ).click();
	await page
		.getByRole( 'radio', { name: 'Sort descending', exact: true } )
		.click();
	await page.keyboard.press( 'Escape' );
	// Assert.
	await expect( page.getByRole( 'row' ).nth( 1 ) ).toContainText(
		'Outlet editor product 30'
	);
} );

test( 'publishes the outlet page', async ( { page } ) => {
	// Arrange.
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	// Act.
	await page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Publish', exact: true } )
		.click();
	await review.getByRole( 'button', { name: 'Save', exact: true } ).click();
	// Assert.
	await expect( save ).not.toHaveClass( /is-busy/ );
	await expect( save ).toBeDisabled();
	await expect(
		page
			.getByRole( 'complementary', { name: 'Page settings' } )
			.getByText( 'Published', { exact: true } )
	).toBeVisible();
} );

test( 'unpublishes the outlet page', async ( { page } ) => {
	// Arrange.
	const sidebar = page.getByRole( 'complementary', {
		name: 'Page settings',
	} );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	// Act.
	await sidebar
		.getByRole( 'button', { name: 'Published', exact: true } )
		.click();
	const draft = page
		.getByRole( 'dialog', { name: 'Status', exact: true } )
		.getByRole( 'radio', { name: 'Draft', exact: true } );
	await draft.check();
	await expect( review ).toHaveCount( 0 );
	await draft.press( 'Escape' );
	await save.click();
	await review.getByRole( 'button', { name: 'Save', exact: true } ).click();
	// Assert.
	await expect( save ).not.toHaveClass( /is-busy/ );
	await expect( save ).toBeDisabled();
	await expect( sidebar.getByText( 'Draft', { exact: true } ) ).toBeVisible();
} );

test( 'views markdown levels', async ( { page } ) => {
	// Assert.
	await expect(
		page.getByRole( 'button', { name: 'Add markdown level' } )
	).toBeVisible();
	await expect(
		page.getByRole( 'button', { name: /^\d+% off$/ } )
	).toHaveText( [ '30% off', '50% off', '70% off' ] );
} );

test( 'adds a markdown level', async ( { page } ) => {
	// Arrange.
	const levels = page.getByRole( 'button', { name: /^\d+% off$/ } );
	const initialLevels = await levels.allTextContents();
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	// Act.
	await page.getByRole( 'button', { name: 'Add markdown level' } ).click();
	await page.getByRole( 'spinbutton', { name: 'Percentage' } ).fill( '40' );
	await page.getByRole( 'button', { name: 'Add', exact: true } ).click();
	await save.click();
	await review.getByRole( 'button', { name: 'Save', exact: true } ).click();
	// Assert.
	await expect(
		page.getByText( 'Site updated.', { exact: true } )
	).toBeVisible();
	await expect( save ).toBeDisabled();
	await expect(
		page.getByRole( 'button', { name: /^40% off$/ } )
	).toBeEnabled();
	await expect( levels ).toHaveCount( initialLevels.length + 1 );
	for ( const level of initialLevels ) {
		await expect(
			page.getByRole( 'button', { name: level, exact: true } )
		).toBeVisible();
	}
} );

test( 'edits a markdown level', async ( { page } ) => {
	// Arrange.
	const levels = page.getByRole( 'button', { name: /^\d+% off$/ } );
	const initialLevels = await levels.allTextContents();
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	// Act.
	const levelButton = page.getByRole( 'button', { name: /^30% off$/ } );
	await expect( levelButton ).toBeEnabled();
	await levelButton.press( 'Enter' );
	const percentage = page.getByRole( 'spinbutton', { name: 'Percentage' } );
	await expect( percentage ).toBeFocused();
	await percentage.fill( '60' );
	await percentage.press( 'Enter' );
	await expect(
		page.getByRole( 'button', { name: /^60% off$/ } )
	).toBeFocused();
	await save.click();
	await review.getByRole( 'button', { name: 'Save', exact: true } ).click();
	// Assert.
	await expect(
		page.getByText( 'Site updated.', { exact: true } )
	).toBeVisible();
	await expect( save ).toBeDisabled();
	await expect(
		page.getByRole( 'button', { name: /^60% off$/ } )
	).toBeEnabled();
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

test( 'deletes a markdown level', async ( { page } ) => {
	// Arrange.
	const levels = page.getByRole( 'button', { name: /^\d+% off$/ } );
	const initialLevels = await levels.allTextContents();
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	// Act.
	await page.getByRole( 'button', { name: /^70% off$/ } ).hover();
	await page
		.getByRole( 'region', { name: 'Markdown Levels' } )
		.getByRole( 'button', { name: 'Options', exact: true } )
		.last()
		.click();
	await page.getByRole( 'menuitem', { name: 'Remove 70% off' } ).click();
	await save.click();
	await review.getByRole( 'button', { name: 'Save', exact: true } ).click();
	// Assert.
	await expect(
		page.getByText( 'Site updated.', { exact: true } )
	).toBeVisible();
	await expect( save ).toBeDisabled();
	await expect(
		page.getByRole( 'button', { name: 'Add markdown level' } )
	).toBeEnabled();
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

test( 'adds a price level', async ( { page, requestUtils } ) => {
	// Arrange.
	const prices = page.getByRole( 'region', { name: 'Price Levels' } );
	const levels = prices.getByRole( 'button', { name: /^Under / } );
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	// Act.
	await prices.getByRole( 'button', { name: 'Add price level' } ).click();
	await page.getByRole( 'spinbutton', { name: 'Price' } ).fill( '1250' );
	await page.getByRole( 'button', { name: 'Add', exact: true } ).click();
	await save.click();
	await review.getByRole( 'button', { name: 'Save', exact: true } ).click();
	// Assert.
	await expect( review ).toHaveCount( 0 );
	await expect( levels ).toHaveCount( 4 );
	await expect( levels.last() ).toBeEnabled();
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	expect( settings.outletpro_price_levels ).toEqual( [ 10, 25, 50, 1250 ] );
} );

test( 'edits a price level', async ( { page, requestUtils } ) => {
	// Arrange.
	const levels = page
		.getByRole( 'region', { name: 'Price Levels' } )
		.getByRole( 'button', { name: /^Under / } );
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	// Act.
	await levels.last().press( 'Enter' );
	await expect(
		page.getByRole( 'spinbutton', { name: 'Price' } )
	).toBeFocused();
	await page.getByRole( 'spinbutton', { name: 'Price' } ).fill( '1500' );
	await page.getByRole( 'spinbutton', { name: 'Price' } ).press( 'Enter' );
	await expect( levels.last() ).toBeFocused();
	await save.click();
	await review.getByRole( 'button', { name: 'Save', exact: true } ).click();
	// Assert.
	await expect( review ).toHaveCount( 0 );
	await expect( levels ).toHaveCount( 4 );
	await expect( levels.last() ).toBeEnabled();
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	expect( settings.outletpro_price_levels ).toEqual( [ 10, 25, 50, 1500 ] );
} );

test( 'removes a price level', async ( { page, requestUtils } ) => {
	// Arrange.
	const prices = page.getByRole( 'region', { name: 'Price Levels' } );
	const levels = prices.getByRole( 'button', { name: /^Under / } );
	const markdownLevels = await page
		.getByRole( 'button', { name: /^\d+% off$/ } )
		.allTextContents();
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
	// Act.
	await levels.last().hover();
	await prices
		.getByRole( 'button', { name: 'Options', exact: true } )
		.last()
		.click();
	await page.getByRole( 'menuitem', { name: /^Remove Under / } ).click();
	await save.click();
	await review.getByRole( 'button', { name: 'Save', exact: true } ).click();
	// Assert.
	await expect( review ).toHaveCount( 0 );
	await expect( levels ).toHaveCount( 3 );
	await expect(
		prices.getByRole( 'button', { name: 'Add price level' } )
	).toBeEnabled();
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	expect( settings.outletpro_price_levels ).toEqual( [ 10, 25, 50 ] );
	expect(
		settings.outletpro_markdown_levels.map( ( level ) => `${ level }% off` )
	).toEqual( markdownLevels );
} );

test( 'edits and saves global badge settings from the Badge tab', async ( {
	page,
	requestUtils,
} ) => {
	// Arrange.
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	const save = page
		.getByRole( 'toolbar', { name: 'Page actions' } )
		.getByRole( 'button', { name: 'Save', exact: true } );
	const review = page.getByRole( 'dialog', {
		name: 'Are you ready to save?',
	} );
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
			page.getByRole( 'button', { name: 'Add markdown level' } )
		).toBeVisible();
		await page.getByRole( 'tab', { name: 'Badge' } ).click();
		await save.click();
		await review
			.getByRole( 'button', { name: 'Save', exact: true } )
			.click();
		await expect( review ).toHaveCount( 0 );
		await expect( save ).toBeDisabled();
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
		await page.setViewportSize( { width: 1280, height: 900 } );
		await page.reload();
		await expect( page.getByRole( 'row' ).nth( 1 ) ).toBeVisible();
	}
} );

test( 'shows a product loading error', async ( { page } ) => {
	// Arrange.
	await page.route( '**/*', async ( route ) => {
		const url = new URL( route.request().url() );
		const path =
			url.searchParams.get( 'rest_route' ) ??
			url.pathname.replace( /^.*\/wp-json/, '' );
		if ( path !== '/wc/v3/products' ) {
			return route.continue();
		}
		return route.fulfill( {
			status: 500,
			contentType: 'application/json',
			body: JSON.stringify( {
				code: 'failed',
				message: 'Products unavailable.',
			} ),
		} );
	} );
	// Act.
	await page
		.getByRole( 'searchbox', { name: 'Search products by name or SKU' } )
		.fill( 'Unavailable products' );
	// Assert.
	await expect(
		page
			.getByRole( 'main', { name: 'Outlet workspace' } )
			.getByText( 'Products could not be loaded.', { exact: true } )
	).toBeVisible();
	await expect( page.getByRole( 'button', { name: 'Retry' } ) ).toHaveCount(
		0
	);
} );
