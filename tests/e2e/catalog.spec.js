/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'Outlet shares the catalog and edits its introduction in a template part', async ( {
	page,
	admin,
	editor,
	requestUtils,
	browser,
} ) => {
	// Arrange.
	const runId = Date.now();
	const settings = await requestUtils.rest( { path: '/wp/v2/settings' } );
	const visibility = await requestUtils.rest( {
		path: '/wc-admin/options',
		params: { options: 'woocommerce_coming_soon' },
	} );
	const [ theme ] = await requestUtils.rest( {
		path: '/wp/v2/themes',
		params: { status: 'active' },
	} );
	const introText = `Catalog introduction ${ runId }`;
	const originalPart = theme.is_block_theme
		? await requestUtils.rest( {
				path: `/wp/v2/template-parts/${ theme.stylesheet }//outletpro-outlet-page`,
				params: { context: 'edit' },
			} )
		: null;
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
			template: '',
			status: 'publish',
			content: theme.is_block_theme
				? ''
				: `<!-- wp:paragraph --><p>${ introText }</p><!-- /wp:paragraph -->`,
		},
	} );
	const products = [];
	const customer = await browser.newContext( {
		storageState: { cookies: [], origins: [] },
	} );
	const storefront = await customer.newPage();
	try {
		await requestUtils.rest( {
			method: 'POST',
			path: '/wc-admin/options',
			data: { woocommerce_coming_soon: 'no' },
		} );
		if ( originalPart !== null ) {
			await requestUtils.rest( {
				method: 'POST',
				path: `/wp/v2/template-parts/${ originalPart.id }`,
				data: {
					content: originalPart.content.raw.replace(
						/(<div[^>]*>)/,
						`$1<!-- wp:paragraph --><p>${ introText }</p><!-- /wp:paragraph -->`
					),
				},
			} );
		}
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
		await expect(
			storefront.getByRole( 'link', { name: 'Edit Outlet section' } )
		).toHaveCount( 0 );
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
			storefront.getByText( introText, { exact: true } )
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
		if ( theme.is_block_theme ) {
			const titleBox = await storefront
				.getByRole( 'heading', {
					name: outlet.title.raw ?? outlet.title.rendered,
					exact: true,
				} )
				.boundingBox();
			const introductionBox = await storefront
				.locator( '.outletpro-navigation' )
				.getByText( introText, { exact: true } )
				.boundingBox();
			const tilesBox = await storefront
				.locator( '.wp-block-outletpro-outlet-tiles' )
				.boundingBox();
			expect( tilesBox.x ).toBeGreaterThanOrEqual( titleBox.x - 1 );
			expect( tilesBox.x + tilesBox.width ).toBeLessThanOrEqual(
				titleBox.x + titleBox.width + 1
			);
			expect( titleBox.y + titleBox.height ).toBeLessThanOrEqual(
				introductionBox.y
			);
			expect(
				introductionBox.y + introductionBox.height
			).toBeLessThanOrEqual( tilesBox.y );
		}
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
			storefront.getByText( introText, { exact: true } )
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

		// The Page editor shows its guidance and a sidebar callout for the selected template.
		await admin.visitAdminPage(
			'post.php',
			`post=${ outlet.id }&action=edit`
		);
		await expect(
			editor.canvas.locator( '.wp-block-template-part' )
		).toHaveCount( 0 );
		await expect(
			editor.canvas.locator( '.wp-block-woocommerce-product-collection' )
		).toHaveCount( 0 );
		expect(
			( await editor.getBlocks() ).map( ( block ) => block.name )
		).toEqual( theme.is_block_theme ? [] : [ 'core/paragraph' ] );

		if ( theme.is_block_theme ) {
			const title = editor.canvas.getByRole( 'textbox', {
				name: 'Add title',
			} );
			await expect( title ).toBeEditable();
			await expect(
				editor.canvas.getByRole( 'button', {
					name: 'Add default block',
				} )
			).toHaveCount( 0 );
			await title.press( 'End' );
			await title.press( 'Enter' );
			expect( await editor.getBlocks() ).toEqual( [] );
			await editor.openDocumentSettingsSidebar();
			await page
				.getByRole( 'tab', { name: 'Page', exact: true } )
				.click();
			await expect(
				page
					.getByRole( 'region', { name: 'Editor settings' } )
					.getByText( 'Template', { exact: true } )
			).toBeVisible();
			const outletNotice = page.locator( '.components-notice' ).filter( {
				hasText: 'Displays products in the outlet.',
			} );
			await expect( outletNotice ).toHaveCount( 0 );
			const sidebar = page.getByRole( 'region', {
				name: 'Editor settings',
			} );
			const description = sidebar.locator(
				'.outletpro-page-description'
			);
			await expect( description ).toHaveCount( 1 );
			await expect( description.locator( ':scope > p' ) ).toHaveText(
				'Displays products in the outlet.'
			);
			const headingBox = await sidebar
				.locator( '.editor-post-card-panel__header' )
				.boundingBox();
			const descriptionBox = await description.boundingBox();
			const featuredImageBox = await sidebar
				.getByRole( 'button', {
					name: 'Set featured image',
					exact: true,
				} )
				.boundingBox();
			expect( headingBox.y + headingBox.height ).toBeLessThanOrEqual(
				descriptionBox.y
			);
			expect(
				descriptionBox.y + descriptionBox.height
			).toBeLessThanOrEqual( featuredImageBox.y );
			await sidebar
				.getByRole( 'button', { name: 'Close Settings' } )
				.click();
			await expect( description ).not.toBeVisible();
			await editor.openDocumentSettingsSidebar();
			await page
				.getByRole( 'tab', { name: 'Page', exact: true } )
				.click();
			await expect( description ).toHaveCount( 1 );
			await expect( description ).toBeVisible();
			await page
				.getByRole( 'tab', { name: 'Block', exact: true } )
				.click();
			await expect( description ).not.toBeVisible();
			await page
				.getByRole( 'tab', { name: 'Page', exact: true } )
				.click();
			await expect( description ).toHaveCount( 1 );
			await expect( description ).toBeVisible();
			const templateGuidance = page
				.getByRole( 'region', { name: 'Editor settings' } )
				.locator( '.outletpro-page-editor-callout' );
			await expect( templateGuidance ).toHaveCount( 1 );
			await expect(
				description.locator( '.outletpro-page-editor-callout' )
			).toHaveCount( 1 );
			const messageBox = await description
				.locator( ':scope > p' )
				.boundingBox();
			const calloutBox = await templateGuidance.boundingBox();
			expect( messageBox.y + messageBox.height ).toBeLessThanOrEqual(
				calloutBox.y
			);
			expect( calloutBox.y + calloutBox.height ).toBeLessThanOrEqual(
				featuredImageBox.y
			);
			await expect(
				templateGuidance.getByRole( 'heading', {
					name: 'Outlet page template',
				} )
			).toBeVisible();
			await expect( templateGuidance ).toContainText(
				'Edit the Outlet section for its introduction and navigation.'
			);
			await expect(
				templateGuidance.getByRole( 'link', {
					name: 'Edit Product Catalog',
				} )
			).toBeVisible();
			await expect(
				templateGuidance.getByRole( 'link', {
					name: 'Edit Outlet section',
				} )
			).toBeVisible();
			await expect(
				page.getByRole( 'link', { name: 'Edit Outlet section' } )
			).toHaveClass( /is-secondary/ );
			await expect(
				editor.canvas.getByText( introText, { exact: true } )
			).toHaveCount( 0 );
			await sidebar.locator( '.editor-post-card-panel' ).screenshot( {
				path: `artifacts/catalog-${ theme.stylesheet }-description.png`,
			} );
		}
		await page.screenshot( {
			path: `artifacts/catalog-${ theme.stylesheet }-editor.png`,
			fullPage: true,
		} );
		if ( theme.is_block_theme ) {
			const savedTemplate = await requestUtils.rest( {
				path: `/wp/v2/templates/${ theme.stylesheet }//archive-product`,
				params: { context: 'edit' },
			} );
			expect( savedTemplate.content.raw ).toContain( 'wp:query-title' );
			expect( savedTemplate.content.raw ).toContain(
				'"conditionType":"page"'
			);
			expect( savedTemplate.content.raw ).toContain(
				'outletpro-outlet-page'
			);
			expect( savedTemplate.content.raw ).toContain( '"inherit":true' );
			expect( savedTemplate.content.raw ).not.toContain(
				'"outletpro":true'
			);
			const savedPart = await requestUtils.rest( {
				path: `/wp/v2/template-parts/${ theme.stylesheet }//outletpro-outlet-page`,
				params: { context: 'edit' },
			} );
			expect( savedPart.content.raw ).toContain(
				'wp:outletpro/outlet-menu'
			);
			expect( savedPart.content.raw ).toContain(
				`<p>${ introText }</p>`
			);
			expect( savedPart.content.raw ).not.toContain(
				'wp:woocommerce/product-collection'
			);

			// The section link opens its editable template part in the native Site Editor.
			await page
				.getByRole( 'link', { name: 'Edit Outlet section' } )
				.click();
			const layoutPage = page;
			try {
				await expect(
					layoutPage.locator( '.outletpro-page-description' )
				).toHaveCount( 0 );
				const layoutCanvas = layoutPage.frameLocator(
					'iframe[name="editor-canvas"]'
				);
				await expect(
					layoutCanvas.locator( '.outletpro-navigation' )
				).toBeVisible();
				const introduction = layoutCanvas
					.locator( '.outletpro-navigation' )
					.getByText( introText, { exact: true } );
				await expect( introduction ).toBeVisible();
				await expect( introduction ).toHaveAttribute(
					'contenteditable',
					'true'
				);
				await expect
					.poll( () =>
						layoutPage.evaluate( () =>
							window.wp.data
								.select( 'core/block-editor' )
								.canInsertBlockType( 'core/paragraph' )
						)
					)
					.toBe( true );
				await expect(
					layoutCanvas.locator( '.wp-block-outletpro-outlet-menu' )
				).toBeVisible();
				await expect(
					layoutCanvas.locator(
						'.wp-block-woocommerce-product-collection'
					)
				).toHaveCount( 0 );
				await expect(
					layoutCanvas.getByText(
						/unsupported|doesn.t include support/i
					)
				).toHaveCount( 0 );
			} finally {
				await admin.visitAdminPage(
					'post.php',
					`post=${ outlet.id }&action=edit`
				);
			}
			await page
				.getByRole( 'link', { name: 'Edit Product Catalog' } )
				.click();
			const catalogCanvas = page.frameLocator(
				'iframe[name="editor-canvas"]'
			);
			const conditionLabel = catalogCanvas.getByText(
				'Displays on outlet page only',
				{ exact: true }
			);
			await expect( conditionLabel ).toBeVisible();
			await expect( conditionLabel ).toHaveAttribute(
				'contenteditable',
				'false'
			);
			await expect( conditionLabel ).toHaveCSS( 'position', 'absolute' );
			await expect( conditionLabel ).toHaveCSS(
				'pointer-events',
				'none'
			);
			await expect( conditionLabel ).toHaveCSS( 'opacity', '0.75' );
			await expect(
				storefront.locator( '.outletpro-conditional-label' )
			).toHaveCount( 0 );
			await page.screenshot( {
				path: `artifacts/catalog-${ theme.stylesheet }-condition.png`,
			} );
		}
		url.searchParams.delete( 'min_price' );
		await storefront.goto( url.href );
		await expect(
			storefront.getByText( introText, {
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
			storefront.getByText( introText, {
				exact: true,
			} )
		).toBeVisible();
		await storefront.screenshot( {
			path: `artifacts/catalog-${ theme.stylesheet }-storefront.png`,
			fullPage: true,
		} );

		if ( theme.is_block_theme ) {
			const desktopViewport = storefront.viewportSize();
			await storefront.setViewportSize( { width: 390, height: 844 } );
			const mobileTiles = await storefront
				.locator( '.wp-block-outletpro-outlet-tiles' )
				.boundingBox();
			expect( mobileTiles.x ).toBeGreaterThan( 0 );
			expect( mobileTiles.x + mobileTiles.width ).toBeLessThan( 390 );
			await storefront.screenshot( {
				path: `artifacts/catalog-${ theme.stylesheet }-mobile.png`,
				fullPage: true,
			} );
			await storefront.setViewportSize( desktopViewport );
		}

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
		await requestUtils.rest( {
			method: 'POST',
			path: '/wc-admin/options',
			data: visibility,
		} );
		if ( originalPart !== null ) {
			await requestUtils.rest( {
				method: 'POST',
				path: `/wp/v2/template-parts/${ originalPart.id }`,
				data: { content: originalPart.content.raw },
			} );
		}
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
