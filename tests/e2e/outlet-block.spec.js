import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'outlet block shows outlet products in editor and on front end', async ( {
	page,
	admin,
	editor,
	requestUtils,
} ) => {
	// Arrange: create 5 products with a unique run ID to avoid cross-run collisions.
	const runId = Date.now();
	const products = await Promise.all(
		[ 1, 2, 3, 4, 5 ].map( ( productNumber ) =>
			requestUtils.rest( {
				method: 'POST',
				path: '/wc/v3/products',
				data: {
					name: `Outlet Block Test Product ${ productNumber } ${ runId }`,
					type: 'simple',
					status: 'publish',
				},
			} )
		)
	);
	const nonOutletProductName = products[ 2 ].name;

	for ( const product of [ products[ 0 ], products[ 1 ] ] ) {
		await admin.visitAdminPage(
			'post.php',
			`post=${ product.id }&action=edit`
		);
		await page.getByRole( 'link', { name: 'Inventory' } ).click();
		await page.getByRole( 'checkbox', { name: 'Outlet' } ).check();
		await page.getByRole( 'button', { name: 'Update' } ).click();
		await page.waitForLoadState( 'networkidle' );
	}

	// Disable the 'Choose a pattern' starter-patterns modal before the editor
	// loads.  The preference is persisted in user meta and read by WordPress on
	// page load, so setting it here (before createNewPost navigates) prevents
	// the modal from ever opening.
	await requestUtils.rest( {
		method: 'PUT',
		path: '/wp/v2/users/me',
		data: {
			meta: {
				persisted_preferences: {
					core: { enableChoosePatternModal: false },
				},
			},
		},
	} );

	// Act: open a new page in the page editor.
	await admin.createNewPost( { postType: 'page' } );
	const canvas = page.frameLocator( 'iframe[name="editor-canvas"]' );

	// Insert a product collection block, then choose "create your own" so
	// WooCommerce sets the default query attributes (filterable, Default query
	// type), and finally enable the outlet toggle in the Advanced panel.
	await editor.insertBlock( { name: 'woocommerce/product-collection' } );

	// The block opens a pattern-picker modal; dismiss it by choosing "create your own".
	await editor.canvas
		.getByRole( 'button', { name: /create your own/i } )
		.click();

	// Open the Advanced inspector panel and enable the outlet toggle.
	await page.getByRole( 'button', { name: 'Advanced' } ).click();
	await page
		.getByRole( 'checkbox', { name: 'Show outlet products only' } )
		.check();

	// Set products per page to 99.
	const productsPerPageInput = page
		.getByLabel( 'Products per page' )
		.and( page.locator( 'input[type="number"]' ) );
	await productsPerPageInput.fill( '99' );

	// Assert: outlet products shown in editor; use .first() because the product
	// collection block renders the title as both a link and an image overlay link.
	await expect(
		editor.canvas
			.getByRole( 'link', {
				name: `Outlet Block Test Product 1 ${ runId }`,
			} )
			.first()
	).toBeVisible();
	await expect(
		editor.canvas
			.getByRole( 'link', {
				name: `Outlet Block Test Product 2 ${ runId }`,
			} )
			.first()
	).toBeVisible();
	await expect(
		editor.canvas.getByRole( 'link', {
			name: nonOutletProductName,
		} )
	).toHaveCount( 0 );
	const editorRenderedProductCount = await editor.canvas
		.getByRole( 'link', {
			name: new RegExp( `Outlet Block Test Product \\d+ ${ runId }` ),
		} )
		.count();
	expect( editorRenderedProductCount ).toBeGreaterThanOrEqual( 1 );
	expect( editorRenderedProductCount ).toBeLessThanOrEqual( 99 );

	// Publish the page.
	const pageId = await editor.publishPost();

	// Navigate to the page on the front end.
	const pageData = await requestUtils.rest( {
		method: 'GET',
		path: `/wp/v2/pages/${ pageId }`,
	} );
	await page.goto( pageData.link );

	// Assert: outlet products shown on front end.
	await expect(
		page
			.getByRole( 'link', {
				name: `Outlet Block Test Product 1 ${ runId }`,
			} )
			.first()
	).toBeVisible();
	await expect(
		page
			.getByRole( 'link', {
				name: `Outlet Block Test Product 2 ${ runId }`,
			} )
			.first()
	).toBeVisible();
	await expect(
		page.getByRole( 'link', {
			name: nonOutletProductName,
		} )
	).toHaveCount( 0 );
	const frontEndRenderedProductCount = await page
		.getByRole( 'link', {
			name: new RegExp( `Outlet Block Test Product \\d+ ${ runId }` ),
		} )
		.count();
	expect( frontEndRenderedProductCount ).toBeGreaterThanOrEqual( 1 );
	expect( frontEndRenderedProductCount ).toBeLessThanOrEqual( 99 );
} );

test( 'Outlet badge has default white text and red background', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange: create an outlet product.
	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: 'Badge Color Test Product',
			type: 'simple',
			status: 'publish',
		},
	} );

	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await page.getByRole( 'link', { name: 'Inventory' } ).click();
	await page.getByRole( 'checkbox', { name: 'Outlet' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Act: navigate to the product's front-end page.
	const productData = await requestUtils.rest( {
		method: 'GET',
		path: `/wc/v3/products/${ product.id }`,
	} );
	await page.goto( productData.permalink );

	// Assert.
	const badge = page.locator( '.wc-outlet-badge' );
	await expect( badge ).toBeVisible();
	await expect( badge ).toHaveCSS( 'color', 'rgb(255, 255, 255)' );
	await expect( badge ).toHaveCSS( 'background-color', 'rgb(248, 18, 64)' );
} );
