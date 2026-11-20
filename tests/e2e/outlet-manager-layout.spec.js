/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'keeps pagination below the scrollable product rows', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	const outlet = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: { title: 'Outlet layout test', status: 'draft' },
	} );
	const products = Array.from( { length: 60 }, ( _, index ) => ( {
		id: index + 1,
		name: `Layout product ${ index + 1 }`,
		image: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
		sku: `LAY-${ index + 1 }`,
		price: '$20.00',
		stock_status: 'instock',
		total_sales: index,
		age_days: index,
		manage_stock: false,
		stock_quantity: null,
		status: 'publish',
		outlet: false,
		canEdit: true,
		editUrl: null,
		categories: [],
	} ) );
	await page.route( /outletpro.*products/, ( route ) => {
		const params = new URL( route.request().url() ).searchParams;
		const currentPage = Number.parseInt( params.get( 'page' ) ?? '1', 10 );
		const perPage = Number.parseInt( params.get( 'per_page' ) ?? '25', 10 );
		return route.fulfill( {
			contentType: 'application/json',
			body: JSON.stringify( {
				items: products.slice(
					( currentPage - 1 ) * perPage,
					currentPage * perPage
				),
				totalItems: products.length,
				totalPages: Math.ceil( products.length / perPage ),
				totalOutletItems: 0,
			} ),
		} );
	} );
	await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/settings',
		data: { outletpro_page_id: outlet.id },
	} );
	try {
		// Act.
		await page.setViewportSize( { width: 1440, height: 720 } );
		await admin.visitAdminPage(
			'post.php',
			`post=${ outlet.id }&action=edit`
		);
		await page.getByRole( 'button', { name: 'Close settings' } ).click();
		const rows = page.locator( '.dataviews-layout__container' );
		const footer = page.locator( '.dataviews-footer' );
		const lastToggle = page.getByRole( 'checkbox', {
			name: 'Include Layout product 25 in outlet',
			exact: true,
		} );
		await expect( lastToggle ).toBeAttached();
		for ( const viewport of [
			{ width: 2560, height: 900 },
			{ width: 1440, height: 720 },
			{ width: 900, height: 600 },
			{ width: 600, height: 500 },
		] ) {
			await page.setViewportSize( viewport );
			await rows.evaluate( ( element ) => {
				element.scrollTop = element.scrollHeight;
			} );
			await lastToggle.scrollIntoViewIfNeeded();

			// Assert.
			await expect( footer ).toBeInViewport( { ratio: 1 } );
			expect(
				await rows.evaluate(
					( element ) => element.scrollHeight > element.clientHeight
				)
			).toBe( true );
			const [ rowsBounds, footerBounds, toggleBounds ] =
				await Promise.all( [
					rows.boundingBox(),
					footer.boundingBox(),
					lastToggle.boundingBox(),
				] );
			expect( rowsBounds ).not.toBeNull();
			expect( footerBounds ).not.toBeNull();
			expect( toggleBounds ).not.toBeNull();
			expect( rowsBounds.y + rowsBounds.height ).toBeLessThanOrEqual(
				footerBounds.y + 1
			);
			expect( toggleBounds.y + toggleBounds.height ).toBeLessThanOrEqual(
				footerBounds.y
			);
			await expect( lastToggle ).toBeInViewport( { ratio: 1 } );
			const alignment = await page
				.getByRole( 'row', { name: /Layout product 25/ } )
				.getByRole( 'cell' )
				.last()
				.evaluate( ( cell ) => {
					const toggle = cell.querySelector(
						'.components-form-toggle'
					);
					if ( toggle === null ) {
						throw new Error( 'The outlet toggle is missing.' );
					}
					const cellBounds = cell.getBoundingClientRect();
					const trackBounds = toggle.getBoundingClientRect();
					const cellStyle = window.getComputedStyle( cell );
					const paddingLeft = Number.parseFloat(
						cellStyle.paddingLeft
					);
					const paddingRight = Number.parseFloat(
						cellStyle.paddingRight
					);
					return {
						cellCenter:
							cellBounds.left +
							( cellBounds.width + paddingLeft - paddingRight ) /
								2,
						toggleCenter: trackBounds.left + trackBounds.width / 2,
					};
				} );
			expect( alignment.toggleCenter ).toBeCloseTo(
				alignment.cellCenter,
				0
			);
			for ( const edge of [ 'start', 'end' ] ) {
				await rows.evaluate( ( element, target ) => {
					element.scrollLeft =
						target === 'start' ? 0 : element.scrollWidth;
				}, edge );
				await expect( lastToggle ).toBeInViewport( { ratio: 1 } );
				await lastToggle.click( { trial: true } );
				const heading = page.getByRole( 'columnheader', {
					name: 'Outlet',
					exact: true,
				} );
				await expect( heading ).toBeInViewport( { ratio: 1 } );
				await heading
					.getByRole( 'button', { name: 'Outlet', exact: true } )
					.click( { trial: true } );
			}
			await page.screenshot( {
				path: `artifacts/outlet-manager-footer-${ viewport.width }.png`,
			} );
		}
		await footer.getByRole( 'button', { name: 'Next page' } ).click();
		await expect(
			page.getByRole( 'checkbox', {
				name: 'Include Layout product 26 in outlet',
				exact: true,
			} )
		).toBeAttached();
		await expect(
			footer.getByRole( 'combobox', { name: 'Current page' } )
		).toHaveValue( '2' );
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
