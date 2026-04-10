import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'clearance section block shows clearance products in editor and on front end', async ( {
	page,
	admin,
	editor,
	requestUtils,
} ) => {
	// Arrange: create 5 products, mark 2 as clearance.
	const products = await Promise.all(
		[ 1, 2, 3, 4, 5 ].map( ( productNumber ) =>
			requestUtils.rest( {
				method: 'POST',
				path: '/wc/v3/products',
				data: {
					name: `Clearance Block Test Product ${ productNumber }`,
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
		await page
			.getByRole( 'checkbox', { name: 'Clearance section' } )
			.check();
		await page.getByRole( 'button', { name: 'Update' } ).click();
		await page.waitForLoadState( 'networkidle' );
	}

	// Act: open a new page in the page editor.
	await admin.createNewPost( { postType: 'page' } );

	// Insert the clearance section block.
	await editor.insertBlock( {
		name: 'woocommerce/product-collection',
		attributes: {
			collection: 'wc-clearance/product-collection/clearance',
		},
	} );

	// Assert: 2 products shown in editor.
	await expect(
		editor.canvas.getByText( 'Clearance Block Test Product 1' )
	).toBeVisible();
	await expect(
		editor.canvas.getByText( 'Clearance Block Test Product 2' )
	).toBeVisible();

	// Publish the page.
	const pageId = await editor.publishPost();

	// Navigate to the page on the front end.
	const pageData = await requestUtils.rest( {
		method: 'GET',
		path: `/wp/v2/pages/${ pageId }`,
	} );
	await page.goto( pageData.link );

	// Assert: 2 products shown on front end.
	await expect(
		page.getByText( 'Clearance Block Test Product 1' )
	).toBeVisible();
	await expect(
		page.getByText( 'Clearance Block Test Product 2' )
	).toBeVisible();
} );
