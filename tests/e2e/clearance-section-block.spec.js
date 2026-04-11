import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'clearance section block shows clearance products in editor and on front end', async ( {
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
					name: `Clearance Block Test Product ${ productNumber } ${ runId }`,
					type: 'simple',
					status: 'publish',
				},
			} )
		)
	);

	for ( const product of [ products[ 0 ], products[ 1 ] ] ) {
		await admin.visitAdminPage(
			'post.php',
			`post=${ product.id }&action=edit`
		);
		await page.getByRole( 'link', { name: 'Inventory' } ).click();
		await page
			.getByRole( 'checkbox', { name: 'Clearance section' } )
			.check();
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

	// Insert the clearance section block.
	await editor.insertBlock( {
		name: 'woocommerce/product-collection',
		attributes: {
			collection: 'wc-clearance/product-collection/clearance',
		},
	} );

	// Assert: clearance products shown in editor; use .first() because the product
	// collection block renders the title as both a link and an image overlay link.
	await expect(
		editor.canvas
			.getByRole( 'link', {
				name: `Clearance Block Test Product 1 ${ runId }`,
			} )
			.first()
	).toBeVisible();
	await expect(
		editor.canvas
			.getByRole( 'link', {
				name: `Clearance Block Test Product 2 ${ runId }`,
			} )
			.first()
	).toBeVisible();

	// Publish the page.
	const pageId = await editor.publishPost();

	// Navigate to the page on the front end.
	const pageData = await requestUtils.rest( {
		method: 'GET',
		path: `/wp/v2/pages/${ pageId }`,
	} );
	await page.goto( pageData.link );

	// Assert: clearance products shown on front end.
	await expect(
		page
			.getByRole( 'link', {
				name: `Clearance Block Test Product 1 ${ runId }`,
			} )
			.first()
	).toBeVisible();
	await expect(
		page
			.getByRole( 'link', {
				name: `Clearance Block Test Product 2 ${ runId }`,
			} )
			.first()
	).toBeVisible();
} );

test( 'clearance badge has default black text and yellow background', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange: create a clearance product.
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
	await page.getByRole( 'checkbox', { name: 'Clearance section' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Act: navigate to the product's front-end page.
	const productData = await requestUtils.rest( {
		method: 'GET',
		path: `/wc/v3/products/${ product.id }`,
	} );
	await page.goto( productData.permalink );

	// Assert.
	const badge = page.locator( '.wc-clearance-badge' );
	await expect( badge ).toBeVisible();
	await expect( badge ).toHaveCSS( 'color', 'rgb(34, 34, 34)' );
	await expect( badge ).toHaveCSS( 'background-color', 'rgb(255, 238, 133)' );
} );
