/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'Outlet uses its saved catalog while keeping editable Page content', async ( {
	page,
	admin,
	editor,
	requestUtils,
	browser,
} ) => {
	// Arrange.
	const runId = Date.now();
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	const [ theme ] = await requestUtils.rest( {
		path: '/wp/v2/themes',
		params: { status: 'active' },
	} );
	const category = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products/categories',
		data: { name: `Catalog ${ runId }` },
	} );
	const outlet = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: {
			title: `Clearance ${ runId }`,
			template: theme.is_block_theme ? 'outlet-catalog' : '',
			status: 'publish',
			content: `<!-- wp:paragraph --><p>Navigation ${ runId }</p><!-- /wp:paragraph -->`,
		},
	} );
	const products = [];
	const customer = await browser.newContext( {
		storageState: { cookies: [], origins: [] },
	} );
	const storefront = await customer.newPage();
	try {
		for ( const price of [ '10', '40', '9999' ] ) {
			products.push(
				await requestUtils.rest( {
					method: 'POST',
					path: '/wc/v3/products',
					data: {
						name: `Catalog ${ price } ${ runId }`,
						type: 'simple',
						status: 'publish',
						regular_price: price,
						categories: [ { id: category.id } ],
					},
				} )
			);
		}
		for ( const product of products.slice( 0, 2 ) ) {
			await admin.visitAdminPage(
				'post.php',
				`post=${ product.id }&action=edit`
			);
			await page.getByRole( 'link', { name: 'Inventory' } ).click();
			await page
				.getByRole( 'checkbox', { name: 'Outlet', exact: true } )
				.check();
			await page
				.getByRole( 'button', { name: 'Update', exact: true } )
				.click();
			await page.waitForLoadState( 'load' );
		}
		await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/settings',
			data: { outletpro_page_id: outlet.id },
		} );
		const url = new URL( outlet.link );
		url.searchParams.set( 'outlet_category', category.slug );

		// Act.
		await storefront.goto( url.href );

		// Assert.
		const breadcrumbs = storefront.getByRole( 'navigation', {
			name: 'Breadcrumb',
			exact: true,
		} );
		await expect( breadcrumbs ).toBeVisible();
		await expect( breadcrumbs ).toHaveText(
			`Home / ${ outlet.title.raw ?? outlet.title.rendered }`
		);
		await expect(
			breadcrumbs.getByRole( 'link', { name: 'Home', exact: true } )
		).toHaveAttribute( 'href', /^https?:\/\// );
		await expect(
			storefront.getByRole( 'heading', {
				name: outlet.title.raw ?? outlet.title.rendered,
				exact: true,
			} )
		).toBeVisible();
		await expect(
			storefront.getByText( `Navigation ${ runId }`, { exact: true } )
		).toBeVisible();
		await expect(
			storefront
				.getByRole( 'link', { name: products[ 0 ].name, exact: false } )
				.first()
		).toBeVisible();
		await expect(
			storefront.getByRole( 'link', {
				name: products[ 2 ].name,
				exact: false,
			} )
		).toHaveCount( 0 );
		const navigationBox = await storefront
			.locator( '.outletpro-navigation' )
			.boundingBox();
		const gridBox = await storefront
			.locator( '.wp-block-woocommerce-product-collection, ul.products' )
			.first()
			.boundingBox();
		expect( navigationBox.y + navigationBox.height ).toBeLessThanOrEqual(
			gridBox.y
		);
		if ( theme.stylesheet === 'storefront' ) {
			await expect(
				storefront.locator( '#primary .outletpro-navigation' )
			).toHaveCount( 0 );
		}
		const filters = storefront.locator(
			'.wp-block-woocommerce-product-filters'
		);
		if ( await filters.count() ) {
			const filterBox = await filters.first().boundingBox();
			expect(
				navigationBox.y + navigationBox.height
			).toBeLessThanOrEqual( filterBox.y );
			const priceFilter = storefront
				.locator( '[data-wp-context*="minRange"]' )
				.first();
			if ( await priceFilter.count() ) {
				const range = JSON.parse(
					await priceFilter.getAttribute( 'data-wp-context' )
				);
				expect( range.maxRange ).toBeLessThan( 9999 );
			}
		}
		url.searchParams.set( 'orderby', 'price-desc' );
		await storefront.goto( url.href );
		const productTitles = await storefront
			.locator(
				'.wc-block-product .wp-block-post-title, .woocommerce-loop-product__title'
			)
			.allTextContents();
		expect(
			productTitles.findIndex( ( title ) =>
				title.includes( products[ 1 ].name )
			)
		).toBeLessThan(
			productTitles.findIndex( ( title ) =>
				title.includes( products[ 0 ].name )
			)
		);
		url.searchParams.set( 'min_price', '20' );
		await storefront.goto( url.href );
		await expect(
			storefront.getByRole( 'link', {
				name: products[ 0 ].name,
				exact: false,
			} )
		).toHaveCount( 0 );
		await expect(
			storefront
				.getByRole( 'link', { name: products[ 1 ].name, exact: false } )
				.first()
		).toBeVisible();
		url.searchParams.set( 'min_price', '10000' );
		await storefront.goto( url.href );
		await expect(
			storefront.getByText( `Navigation ${ runId }`, { exact: true } )
		).toBeVisible();
		await expect(
			storefront.locator( '.wc-block-product, ul.products > li.product' )
		).toHaveCount( 0 );

		// The category archive continues to include regular products.
		await storefront.goto( `/?product_cat=${ category.slug }` );
		await expect(
			storefront
				.getByRole( 'link', { name: products[ 2 ].name, exact: false } )
				.first()
		).toBeVisible();
		await expect(
			storefront.locator( '.outletpro-navigation' )
		).toHaveCount( 0 );

		// The Page editor keeps navigation editable inside the full block template.
		await admin.visitAdminPage(
			'post.php',
			`post=${ outlet.id }&action=edit`
		);
		await expect(
			editor.canvas.getByText( `Navigation ${ runId }`, { exact: true } )
		).toBeVisible();
		if ( theme.is_block_theme ) {
			await expect(
				editor.canvas.locator( '.wp-block-template-part' ).first()
			).toBeVisible();
			await expect(
				editor.canvas.locator(
					'.wp-block-woocommerce-product-collection'
				)
			).toBeVisible();
			await expect(
				editor.canvas.getByText(
					/unsupported|doesn.t include support/i
				)
			).toHaveCount( 0 );
			const registrations = await page.evaluate( () =>
				[
					'woocommerce/breadcrumbs',
					'woocommerce/catalog-sorting',
					'woocommerce/product-results-count',
					'woocommerce/legacy-template',
				].map( ( name ) =>
					Boolean( window.wp.blocks.getBlockType( name ) )
				)
			);
			expect( registrations ).toEqual( [ true, true, true, true ] );
			await editor.openDocumentSettingsSidebar();
			await expect(
				page.getByRole( 'link', { name: 'Edit catalog layout' } )
			).toHaveAttribute( 'href', /postId=.*outlet-catalog/ );
		} else {
			await expect(
				editor.canvas.locator(
					'.wp-block-woocommerce-product-collection'
				)
			).toHaveCount( 0 );
		}
		await page.screenshot( {
			path: `artifacts/catalog-${ theme.stylesheet }-editor.png`,
			fullPage: true,
		} );
		const navigation = editor.canvas.getByText( `Navigation ${ runId }`, {
			exact: true,
		} );
		await navigation.fill( `Updated navigation ${ runId }` );
		await page.getByRole( 'button', { name: 'Save', exact: true } ).click();
		await expect(
			page.getByRole( 'button', { name: 'Save', exact: true } )
		).toBeDisabled();
		if ( theme.is_block_theme ) {
			const savedTemplate = await requestUtils.rest( {
				path: `/wp/v2/templates/${ theme.stylesheet }//outlet-catalog`,
				params: { context: 'edit' },
			} );
			expect( savedTemplate.content.raw ).toContain( 'wp:query-title' );
			expect( savedTemplate.content.raw ).toContain( '"inherit":true' );
			expect( savedTemplate.content.raw ).not.toContain(
				'"outletpro":true'
			);
		}
		url.searchParams.delete( 'min_price' );
		await storefront.goto( url.href );
		await expect(
			storefront.getByText( `Updated navigation ${ runId }`, {
				exact: true,
			} )
		).toBeVisible();

		const renamed = await requestUtils.rest( {
			method: 'POST',
			path: `/wp/v2/pages/${ outlet.id }`,
			data: { slug: `renamed-outlet-${ runId }` },
		} );
		const renamedUrl = new URL( renamed.link );
		renamedUrl.search = url.search;
		await storefront.goto( renamedUrl.href );
		await expect(
			storefront.getByText( `Updated navigation ${ runId }`, {
				exact: true,
			} )
		).toBeVisible();
		await storefront.screenshot( {
			path: `artifacts/catalog-${ theme.stylesheet }-storefront.png`,
			fullPage: true,
		} );

		// Publishing restrictions still belong to the Page.
		await requestUtils.rest( {
			method: 'POST',
			path: `/wp/v2/pages/${ outlet.id }`,
			data: { password: 'catalog-test' },
		} );
		await storefront.reload();
		await expect( storefront.getByLabel( /password/i ) ).toBeVisible();
		await expect(
			storefront.getByRole( 'link', {
				name: products[ 0 ].name,
				exact: false,
			} )
		).toHaveCount( 0 );
		await requestUtils.rest( {
			method: 'POST',
			path: `/wp/v2/pages/${ outlet.id }`,
			data: { password: '', status: 'draft' },
		} );
		const draftResponse = await storefront.reload();
		expect( draftResponse.status() ).toBe( 404 );
		await expect(
			storefront.locator( '.outletpro-navigation' )
		).toHaveCount( 0 );
	} finally {
		await customer.close();
		await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/settings',
			data: { outletpro_page_id: settings.outletpro_page_id },
		} );
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wp/v2/pages/${ outlet.id }`,
		} );
		for ( const product of products ) {
			await requestUtils.rest( {
				method: 'DELETE',
				path: `/wc/v3/products/${ product.id }`,
				params: { force: true },
			} );
		}
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wc/v3/products/categories/${ category.id }`,
			params: { force: true },
		} );
	}
} );
