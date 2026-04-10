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
		await page
			.getByRole( 'checkbox', { name: 'Clearance section' } )
			.check();
		await page.getByRole( 'button', { name: 'Update' } ).click();
		await page.waitForLoadState( 'networkidle' );
	}

	// Act: create a published page with the clearance section block via the REST API.
	// Creating the page via REST avoids the flaky WooCommerce-entity publish flow
	// that occurs when editor.publishPost() is called after inserting a
	// woocommerce/product-collection block.
	const testPage = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: {
			title: `Clearance Block Test Page ${ runId }`,
			status: 'publish',
			content:
				'<!-- wp:woocommerce/product-collection {"queryId":0,"query":{"isProductCollectionBlock":true},"collection":"wc-clearance/product-collection/clearance"} -->' +
				'<div class="wp-block-woocommerce-product-collection">' +
				'<!-- wp:woocommerce/product-template -->' +
				'<!-- wp:post-title {"isLink":true} /-->' +
				'<!-- /wp:woocommerce/product-template -->' +
				'</div>' +
				'<!-- /wp:woocommerce/product-collection -->',
		},
	} );

	// Assert: clearance products shown in editor; use .first() because the product
	// collection block renders the title as both a link and an image overlay link.
	await admin.visitAdminPage(
		'post.php',
		`post=${ testPage.id }&action=edit`
	);
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

	// Assert: clearance products shown on front end.
	await page.goto( testPage.link );
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
