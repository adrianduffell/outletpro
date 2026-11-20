/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.use( { viewport: { width: 1440, height: 1000 } } );

test( 'manages products through the editor-style outlet screen', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	const marker = `Outlet manager ${ Date.now() }`;
	const outlet = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: {
			title: marker,
			status: 'draft',
			content: '<p>Preserved content</p>',
		},
	} );
	const first = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: `${ marker } A`,
			status: 'publish',
			regular_price: '18',
			sale_price: '12',
			date_created_gmt: new Date( Date.now() - 37 * 86400000 - 3600000 )
				.toISOString()
				.replace( /\.\d{3}Z$/, '' ),
			manage_stock: true,
			stock_quantity: 8,
		},
	} );
	const second = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: { name: `${ marker } B`, status: 'publish', regular_price: '20' },
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
		await expect(
			page.getByRole( 'toolbar', { name: 'Page actions' } )
		).toBeVisible();
		await expect(
			page.getByRole( 'heading', {
				name: 'Manage outlet products',
				level: 2,
			} )
		).toBeVisible();
		await expect(
			page.getByText( 'Select the products to display on the outlet page.', {
				exact: true,
			} )
		).toBeVisible();
		const controls = page.getByRole( 'group', {
			name: 'Product controls',
		} );
		const controlBounds = await Promise.all( [
			controls
				.getByRole( 'checkbox', { name: 'Show outlet products only' } )
				.boundingBox(),
			controls.getByRole( 'searchbox' ).boundingBox(),
		] );
		for ( const bounds of controlBounds ) {
			expect( bounds ).not.toBeNull();
		}
		const centers = controlBounds.map(
			( bounds ) => bounds.y + bounds.height / 2
		);
		expect(
			Math.max( ...centers ) - Math.min( ...centers )
		).toBeLessThanOrEqual( 1 );
		await expect(
			page.getByRole( 'button', { name: 'Page settings' } )
		).toHaveCSS( 'height', '32px' );
		await expect(
			page.getByRole( 'button', { name: 'Page settings' } )
		).toHaveCSS( 'width', '32px' );
		await expect(
			page.getByRole( 'link', { name: 'Back to pages' } )
		).toHaveCSS( 'width', '32px' );
		await expect( page.locator( '.outletpro-manager-header' ) ).toHaveCSS(
			'height',
			'64px'
		);
		const sidebar = page.getByRole( 'complementary', {
			name: 'Page details',
		} );
		await expect( sidebar ).toHaveCSS( 'width', '280px' );
		const tierPanel = sidebar.locator(
			'.outletpro-manager-discount-tiers'
		);
		const screenConfig = await page
			.locator( '#outletpro-outlet-manager' )
			.evaluate( ( element ) => JSON.parse( element.dataset.config ) );
		expect( Array.isArray( screenConfig.discountTiers ) ).toBe( true );
		await expect(
			tierPanel.getByRole( 'heading', { name: 'Discount levels' } )
		).toBeVisible();
		await expect(
			tierPanel.locator( '.outletpro-manager-tier-list li' )
		).toHaveText( screenConfig.discountTiers.map( ( tier ) => tier.name ) );
		await tierPanel
			.getByRole( 'button', { name: 'Add discount level', exact: true } )
			.click();
		await expect(
			tierPanel.locator( '.outletpro-manager-tier-list li' )
		).toHaveText( screenConfig.discountTiers.map( ( tier ) => tier.name ) );
		await expect(
			page
				.getByRole( 'toolbar', { name: 'Page actions' } )
				.getByRole( 'button', { name: 'Save', exact: true } )
		).toBeDisabled();
		await tierPanel.screenshot( {
			path: 'artifacts/outlet-manager-discount-tiers.png',
		} );

		const pricePanel = sidebar.locator( '.outletpro-manager-price-tiers' );
		expect( Array.isArray( screenConfig.priceBands ) ).toBe( true );
		await expect(
			pricePanel.getByRole( 'heading', { name: 'Price bands' } )
		).toBeVisible();
		await expect(
			pricePanel.locator( '.outletpro-manager-tier-list li' )
		).toHaveText( screenConfig.priceBands.map( ( band ) => band.name ) );
		await pricePanel
			.getByRole( 'button', { name: 'Add price band', exact: true } )
			.click();
		await expect(
			pricePanel.locator( '.outletpro-manager-tier-list li' )
		).toHaveText( screenConfig.priceBands.map( ( band ) => band.name ) );
		await expect(
			page
				.getByRole( 'toolbar', { name: 'Page actions' } )
				.getByRole( 'button', { name: 'Save', exact: true } )
		).toBeDisabled();
		await pricePanel.screenshot( {
			path: 'artifacts/outlet-manager-price-bands.png',
		} );

		await expect( sidebar.getByRole( 'tab', { name: 'Page' } ) ).toHaveCSS(
			'height',
			'48px'
		);
		await expect(
			sidebar.getByRole( 'heading', { name: marker } )
		).toHaveCSS( 'font-size', '13px' );
		await expect(
			sidebar.getByRole( 'heading', { name: marker } )
		).toHaveCSS( 'font-weight', '600' );
		await expect(
			page.locator( '.outletpro-manager-sidebar-content' )
		).toHaveCSS( 'padding', '16px' );
		await expect(
			sidebar.getByRole( 'button', { name: 'Close settings' } )
		).toHaveCSS( 'height', '32px' );
		await sidebar.getByRole( 'button', { name: 'Close settings' } ).click();
		await expect( sidebar ).toHaveCount( 0 );
		await expect(
			page.getByRole( 'button', { name: 'Page settings' } )
		).toBeFocused();
		await page.getByRole( 'button', { name: 'Page settings' } ).click();
		await expect( sidebar ).toBeVisible();
		await page
			.getByRole( 'button', {
				name: 'Search commands and settings',
				exact: true,
			} )
			.click();
		await expect(
			page.getByPlaceholder( 'Search commands and settings' )
		).toBeVisible();
		await page.keyboard.press( 'Escape' );
		await expect( page.locator( '#adminmenumain' ) ).toBeHidden();
		await expect(
			page.locator( 'iframe[name="editor-canvas"]' )
		).toHaveCount( 0 );
		await expect(
			page.getByRole( 'checkbox', {
				name: 'Show outlet products only',
				exact: true,
			} )
		).not.toBeChecked();
		const searchResponse = page.waitForResponse( ( response ) => {
			const params = new URL( response.url() ).searchParams;
			return (
				params.get( 'membership' ) === 'all' &&
				params.get( 'search' ) === marker
			);
		} );
		await page.getByRole( 'searchbox' ).fill( marker );
		await searchResponse;
		await expect( page.getByRole( 'table' ) ).toHaveAttribute(
			'aria-busy',
			'false'
		);
		await expect(
			page.getByRole( 'row', { name: new RegExp( first.name ) } )
		).toBeVisible();
		const salePrice = page
			.getByRole( 'row', { name: new RegExp( first.name ) } )
			.locator( '.outletpro-manager-price' );
		await expect(
			page
				.getByRole( 'row', { name: new RegExp( first.name ) } )
				.getByRole( 'cell' )
				.nth( 6 )
		).toHaveText( 'In stock (8)' );
		await expect(
			page
				.getByRole( 'row', { name: new RegExp( second.name ) } )
				.getByRole( 'cell' )
				.nth( 6 )
		).toHaveText( 'In stock' );
		await expect( salePrice.locator( 'del' ) ).toContainText( '18' );
		await expect( salePrice.locator( 'del' ) ).toHaveCSS(
			'text-decoration-line',
			'line-through'
		);
		await expect( salePrice.locator( 'ins' ) ).toContainText( '12' );
		await expect( salePrice.locator( 'ins' ) ).toHaveCSS(
			'text-decoration-line',
			'none'
		);
		await expect(
			salePrice.locator( '.screen-reader-text' ).first()
		).toHaveCSS( 'position', 'absolute' );
		await expect(
			salePrice.locator( '.screen-reader-text' ).first()
		).toHaveCSS( 'width', '1px' );
		await page
			.getByRole( 'table' )
			.screenshot( { path: 'artifacts/outlet-manager-prices.png' } );
		const headers = page.getByRole( 'columnheader' );
		await expect( headers.nth( 0 ) ).toHaveAccessibleName( 'Image' );
		await expect( headers.nth( 1 ) ).toContainText( 'Product' );
		await expect( headers.nth( 2 ) ).toHaveText( 'SKU' );
		await expect( headers.nth( 3 ) ).toHaveText( 'Total Sales' );
		await expect( headers.nth( 4 ) ).toHaveText( 'Age (days)' );
		await expect( headers.nth( 5 ) ).toHaveText( 'Price' );
		await expect( headers.nth( 6 ) ).toHaveText( 'Stock' );
		await expect( headers.nth( 7 ) ).toHaveText( 'Outlet' );
		await expect( headers ).toHaveCount( 8 );
		await expect(
			page
				.getByRole( 'row', { name: new RegExp( first.name ) } )
				.getByRole( 'cell' )
				.nth( 4 )
		).toHaveText( '37' );
		const thumbnail = page
			.getByRole( 'row', { name: new RegExp( first.name ) } )
			.locator( 'img' );
		await expect( thumbnail ).toHaveCSS( 'width', '48px' );
		await expect( thumbnail ).toHaveCSS( 'height', '48px' );
		await expect
			.poll( () => thumbnail.evaluate( ( image ) => image.naturalWidth ) )
			.toBeGreaterThan( 0 );
		await expect(
			page
				.getByRole( 'row', { name: new RegExp( first.name ) } )
				.getByRole( 'cell' )
				.nth( 3 )
		).toHaveText( '0' );
		await expect(
			page.getByRole( 'columnheader', { name: 'Actions', exact: true } )
		).toHaveCount( 0 );
		await expect(
			page.getByRole( 'table' ).getByRole( 'checkbox' )
		).toHaveCount( 2 );
		const saveButton = page
			.getByRole( 'toolbar', { name: 'Page actions' } )
			.getByRole( 'button', { name: 'Save', exact: true } );
		const review = page.getByRole( 'complementary', {
			name: 'Review changes',
		} );
		const firstToggle = page.getByRole( 'checkbox', {
			name: `Include ${ first.name } in outlet`,
			exact: true,
		} );
		const secondToggle = page.getByRole( 'checkbox', {
			name: `Include ${ second.name } in outlet`,
			exact: true,
		} );
		await firstToggle.check();
		await expect( firstToggle ).toBeChecked();
		await expect( firstToggle ).toBeEnabled();
		await expect( firstToggle ).toBeFocused();
		const unsaved = await requestUtils.rest( {
			path: '/outletpro/v1/products',
			params: { search: marker, membership: 'all' },
		} );
		expect( unsaved.items.every( ( product ) => ! product.outlet ) ).toBe(
			true
		);
		const panelToggle = page.getByRole( 'button', {
			name: 'Manage outlet products',
			exact: true,
		} );
		await panelToggle.click();
		await expect( panelToggle ).toHaveAttribute( 'aria-expanded', 'false' );
		await expect( page.getByRole( 'searchbox' ) ).toBeHidden();
		await page.screenshot( {
			path: 'artifacts/outlet-manager-collapsed.png',
		} );
		await panelToggle.click();
		await expect( page.getByRole( 'searchbox' ) ).toHaveValue( marker );
		await expect( firstToggle ).toBeChecked();
		await page.getByRole( 'searchbox' ).fill( second.name );
		await expect( firstToggle ).toHaveCount( 0 );
		await page.getByRole( 'searchbox' ).fill( marker );
		await expect( firstToggle ).toBeChecked();
		await secondToggle.check();
		await firstToggle.uncheck();
		await saveButton.click();
		await expect(
			review.getByText( second.name, { exact: true } )
		).toBeVisible();
		await expect(
			review.getByText( first.name, { exact: true } )
		).toHaveCount( 0 );

		await expect(
			review.getByText( 'Are you ready to save?' )
		).toBeVisible();
		await expect(
			review.getByRole( 'heading', { name: 'Outlet products' } )
		).toHaveCount( 0 );
		const actions = review.locator( '.outletpro-manager-review-actions' );
		await expect( actions.getByRole( 'button' ).nth( 0 ) ).toHaveText(
			'Cancel'
		);
		await expect( actions.getByRole( 'button' ).nth( 1 ) ).toHaveText(
			'Save'
		);
		await expect( actions ).toHaveCSS( 'height', '64px' );
		const [ cancelBounds, saveBounds, summaryBounds ] = await Promise.all( [
			actions.getByRole( 'button', { name: 'Cancel' } ).boundingBox(),
			actions.getByRole( 'button', { name: 'Save' } ).boundingBox(),
			review.locator( '.outletpro-manager-review-prompt' ).boundingBox(),
		] );
		expect( cancelBounds.width ).toBeCloseTo( saveBounds.width, 0 );
		expect( cancelBounds.y + cancelBounds.height ).toBeLessThan(
			summaryBounds.y
		);
		const reviewChoice = review.getByRole( 'checkbox', {
			name: second.name,
			exact: true,
		} );
		await reviewChoice.uncheck();
		await expect( reviewChoice ).toBeVisible();
		await expect( reviewChoice ).not.toBeChecked();
		await expect( secondToggle ).toBeChecked();
		await expect(
			review.getByRole( 'button', { name: 'Save', exact: true } )
		).toBeDisabled();
		await page.screenshot( {
			path: 'artifacts/outlet-manager-review-unchecked.png',
		} );
		await reviewChoice.check();
		await expect( reviewChoice ).toBeChecked();
		await expect(
			review.getByRole( 'button', { name: 'Save', exact: true } )
		).toBeEnabled();
		await review
			.getByRole( 'button', { name: 'Cancel', exact: true } )
			.click();
		await expect( secondToggle ).toBeChecked();
		await firstToggle.check();

		let releaseSave = () => {};
		const saveGate = new Promise( ( resolve ) => {
			releaseSave = resolve;
		} );
		const batchRoute = /outletpro.*products.*batch/;
		await page.route( batchRoute, async ( route ) => {
			await saveGate;
			await route.continue();
		} );
		await saveButton.click();
		await expect(
			review.getByText( first.name, { exact: true } )
		).toBeVisible();
		await page.screenshot( {
			path: 'artifacts/outlet-manager-review.png',
		} );
		await review
			.getByRole( 'button', { name: 'Save', exact: true } )
			.click();
		try {
			await expect( firstToggle ).toBeDisabled();
			await expect( secondToggle ).toBeDisabled();
			await expect(
				review.getByRole( 'button', { name: 'Save', exact: true } )
			).toBeDisabled();
		} finally {
			releaseSave();
		}
		await expect( review ).toHaveCount( 0 );
		await page.unroute( batchRoute );

		await expect( firstToggle ).toBeChecked();
		await expect( secondToggle ).toBeChecked();
		await expect( page.getByTestId( 'snackbar' ) ).toHaveCount( 0 );
		await page
			.getByRole( 'checkbox', {
				name: 'Show outlet products only',
				exact: true,
			} )
			.check();
		await firstToggle.uncheck();
		await expect( firstToggle ).toBeVisible();
		await saveButton.click();
		await expect( review.getByText( 'Remove from outlet' ) ).toBeVisible();
		await review
			.getByRole( 'button', { name: 'Save', exact: true } )
			.click();
		await expect( review ).toHaveCount( 0 );
		await expect( firstToggle ).toHaveCount( 0 );

		await page.route( batchRoute, ( route ) =>
			route.fulfill( {
				status: 403,
				contentType: 'application/json',
				body: JSON.stringify( {
					code: 'cannot_edit_product',
					message:
						'The product could not be saved. Please try again.',
					data: { status: 403 },
				} ),
			} )
		);
		await secondToggle.uncheck();
		await saveButton.click();
		await review
			.getByRole( 'button', { name: 'Save', exact: true } )
			.click();
		await expect( page.getByTestId( 'snackbar' ) ).toContainText(
			'The product could not be saved. Please try again.'
		);
		await expect(
			review.getByText( second.name, { exact: true } )
		).toBeVisible();
		await expect( secondToggle ).not.toBeChecked();
		await expect( secondToggle ).toBeEnabled();
		await page
			.getByRole( 'button', { name: 'Dismiss this notice', exact: true } )
			.click();
		const removalChoice = review.getByRole( 'checkbox', {
			name: second.name,
			exact: true,
		} );
		await removalChoice.uncheck();
		await expect( removalChoice ).toBeVisible();
		await expect( removalChoice ).not.toBeChecked();
		await expect( secondToggle ).not.toBeChecked();
		await review
			.getByRole( 'button', { name: 'Cancel', exact: true } )
			.click();
		await page.unroute( batchRoute );
		await secondToggle.check();
		await expect( saveButton ).toBeDisabled();
		await page
			.getByRole( 'checkbox', {
				name: 'Show outlet products only',
				exact: true,
			} )
			.uncheck();
		await expect( firstToggle ).not.toBeChecked();
		await page.screenshot( {
			path: 'artifacts/outlet-manager-row-toggles.png',
		} );
		await sidebar
			.getByRole( 'button', { name: 'Page actions', exact: true } )
			.click();
		await page
			.getByRole( 'menuitem', { name: 'Rename', exact: true } )
			.click();
		await page
			.getByRole( 'textbox', { name: 'Name', exact: true } )
			.fill( `${ marker } renamed` );
		await page.getByRole( 'button', { name: 'Done', exact: true } ).click();
		await page
			.getByRole( 'button', { name: 'Edit Slug', exact: true } )
			.click();
		await page
			.getByRole( 'textbox', { name: 'Slug', exact: true } )
			.fill( `outlet-manager-${ outlet.id }` );
		await page.keyboard.press( 'Escape' );
		await saveButton.click();
		await expect(
			review.getByText( `${ marker } renamed`, { exact: true } )
		).toBeVisible();
		await expect(
			review.getByText( `outlet-manager-${ outlet.id }`, { exact: true } )
		).toBeVisible();
		await review
			.getByRole( 'button', { name: 'Save', exact: true } )
			.click();
		await expect( review ).toHaveCount( 0 );
		await page
			.getByRole( 'button', { name: 'Publish', exact: true } )
			.click();
		await expect(
			review.getByText( 'This page will be published.' )
		).toBeVisible();
		await review
			.getByRole( 'button', { name: 'Publish', exact: true } )
			.click();
		await expect( review ).toHaveCount( 0 );
		await expect(
			sidebar.getByText( 'Published', { exact: true } )
		).toBeVisible();

		// Assert.
		const saved = await requestUtils.rest( {
			path: `/wp/v2/pages/${ outlet.id }`,
			params: { context: 'edit' },
		} );
		expect( saved.title.raw ).toBe( `${ marker } renamed` );
		expect( saved.content.raw ).toBe( '<p>Preserved content</p>' );
		expect( saved.status ).toBe( 'publish' );
		const storefront = await page.context().newPage();
		await storefront.goto( saved.link );
		await expect(
			storefront.getByText( second.name, { exact: true } ).first()
		).toBeVisible();
		await expect(
			storefront.getByText( first.name, { exact: true } )
		).toHaveCount( 0 );
		await storefront.close();
		await page.getByRole( 'button', { name: 'Edit Status' } ).click();
		await page
			.getByRole( 'button', { name: 'Unpublish', exact: true } )
			.click();
		await expect(
			review.getByText( 'This page will be unpublished.' )
		).toBeVisible();
		await review
			.getByRole( 'button', { name: 'Save', exact: true } )
			.click();
		await expect( review ).toHaveCount( 0 );
		await expect(
			page.getByRole( 'button', { name: 'Publish', exact: true } )
		).toBeVisible();
	} finally {
		await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/settings',
			data: { outletpro_page_id: settings.outletpro_page_id },
		} );
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wc/v3/products/${ first.id }`,
			params: { force: true },
		} );
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wc/v3/products/${ second.id }`,
			params: { force: true },
		} );
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wp/v2/pages/${ outlet.id }`,
			params: { force: true },
		} );
	}
} );

test( 'preserves the standard editor for legacy outlet pages', async ( {
	page,
	requestUtils,
} ) => {
	// Arrange.
	const [ activeTheme ] = await requestUtils.rest( {
		path: '/wp/v2/themes',
		params: { status: 'active' },
	} );
	test.skip(
		! activeTheme.is_block_theme,
		'The legacy outlet template is only available in block themes.'
	);
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	const outlet = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: {
			title: 'Legacy outlet editor',
			status: 'draft',
			template: 'outlet-page',
		},
	} );
	await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/settings',
		data: { outletpro_page_id: outlet.id },
	} );
	try {
		// Act.
		// The existing legacy editor emits a known core warning with WP_DEBUG enabled.
		await page.goto( `/wp-admin/post.php?post=${ outlet.id }&action=edit` );

		// Assert.
		await expect( page.locator( '#outletpro-outlet-manager' ) ).toHaveCount(
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
