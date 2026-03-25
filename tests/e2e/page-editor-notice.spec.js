import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Deletes all products via the WC REST API.
 *
 * @param {Object} requestUtils - Playwright request utilities.
 */
async function deleteAllProducts( requestUtils ) {
	const products = await requestUtils.rest( {
		method: 'GET',
		path: '/wc/v3/products',
		params: {
			per_page: 100,
		},
	} );
	for ( const product of products ) {
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wc/v3/products/${ product.id }`,
		} );
	}
}

/**
 * Gets the clearance page ID from the WP REST API settings endpoint.
 *
 * @param {Object} requestUtils - Playwright request utilities.
 * @return {Promise<number>} The clearance page ID.
 */
async function getClearancePageId( requestUtils ) {
	const settings = await requestUtils.rest( {
		method: 'GET',
		path: '/wp/v2/settings',
	} );
	return settings.wc_clearance_page_id;
}

test( 'notice appears in page editor when clearance section has no products', async ( {
	page,
	admin,
	editor,
	requestUtils,
} ) => {
	// Arrange: delete all products so no clearance products exist.
	await deleteAllProducts( requestUtils );
	const clearancePageId = await getClearancePageId( requestUtils );

	// Act: open the clearance page in the block editor.
	await admin.visitAdminPage(
		'post.php',
		`post=${ clearancePageId }&action=edit`
	);
	await editor.setPreferences( 'core/edit-post', {
		welcomeGuide: false,
		fullscreenMode: false,
	} );

	// Assert: the warning notice is visible in the editor.
	await expect(
		page.getByText(
			'The clearance section has no products. Include products to display them on this page.'
		)
	).toBeVisible();
} );

test( 'notice links to the product list screen', async ( {
	page,
	admin,
	editor,
	requestUtils,
} ) => {
	// Arrange: delete all products so no clearance products exist.
	await deleteAllProducts( requestUtils );
	const clearancePageId = await getClearancePageId( requestUtils );

	// Act: open the clearance page in the block editor.
	await admin.visitAdminPage(
		'post.php',
		`post=${ clearancePageId }&action=edit`
	);
	await editor.setPreferences( 'core/edit-post', {
		welcomeGuide: false,
		fullscreenMode: false,
	} );

	// Assert: the "Learn how" link points to the products screen.
	const learnHowLink = page.getByRole( 'link', { name: 'Learn how' } );
	await expect( learnHowLink ).toBeVisible();
	await expect( learnHowLink ).toHaveAttribute(
		'href',
		/edit\.php\?post_type=product/
	);
} );

test( 'notice does not appear when clearance section has products', async ( {
	page,
	admin,
	editor,
	requestUtils,
} ) => {
	// Arrange: create a product and mark it as clearance via the admin UI.
	const product = await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/products',
		data: {
			name: 'Test Clearance Product',
			type: 'simple',
			status: 'publish',
		},
	} );
	await admin.visitAdminPage(
		'post.php',
		`post=${ product.id }&action=edit`
	);
	await page.getByRole( 'checkbox', { name: 'Clearance section' } ).check();
	await page.getByRole( 'button', { name: 'Update' } ).click();
	await page.waitForLoadState( 'networkidle' );

	const clearancePageId = await getClearancePageId( requestUtils );

	// Act: open the clearance page in the block editor.
	await admin.visitAdminPage(
		'post.php',
		`post=${ clearancePageId }&action=edit`
	);
	await editor.setPreferences( 'core/edit-post', {
		welcomeGuide: false,
		fullscreenMode: false,
	} );

	// Assert: the notice is not shown when there are clearance products.
	await expect(
		page.getByText(
			'The clearance section has no products. Include products to display them on this page.'
		)
	).not.toBeVisible();

	// Cleanup.
	await requestUtils.rest( {
		method: 'DELETE',
		path: `/wc/v3/products/${ product.id }`,
		data: { force: true },
	} );
} );

test( 'notice does not appear on other pages', async ( {
	page,
	admin,
	editor,
	requestUtils,
} ) => {
	// Arrange: delete all products and create a non-clearance page.
	await deleteAllProducts( requestUtils );
	const otherPage = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: {
			title: 'Other Page',
			status: 'draft',
		},
	} );

	// Act: open a non-clearance page in the block editor.
	await admin.visitAdminPage(
		'post.php',
		`post=${ otherPage.id }&action=edit`
	);
	await editor.setPreferences( 'core/edit-post', {
		welcomeGuide: false,
		fullscreenMode: false,
	} );

	// Assert: the notice is not shown on non-clearance pages.
	await expect(
		page.getByText(
			'The clearance section has no products. Include products to display them on this page.'
		)
	).not.toBeVisible();

	// Cleanup.
	await requestUtils.rest( {
		method: 'DELETE',
		path: `/wp/v2/pages/${ otherPage.id }`,
		data: { force: true },
	} );
} );
