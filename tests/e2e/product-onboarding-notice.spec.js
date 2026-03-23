import { test, expect } from '@wordpress/e2e-test-utils-playwright';

const STORAGE_KEY = 'wc_clearance_product_onboarding_dismissed';

test.beforeEach( async ( { page, admin } ) => {
	// Navigate to the product list to establish the correct origin, then
	// remove the dismissal key so each test starts with the notice un-dismissed.
	await admin.visitAdminPage( 'edit.php', 'post_type=product' );
	await page.evaluate( ( key ) => localStorage.removeItem( key ), STORAGE_KEY );
} );

test( 'notice shows when there are no clearance products', async ( {
	page,
	admin,
} ) => {
	// Act.
	await admin.visitAdminPage( 'edit.php', 'post_type=product' );

	// Assert.
	await expect(
		page.locator( '.wc-clearance-onboarding-notice' )
	).toBeVisible();
} );

test( 'notice is not shown when a clearance product exists', async ( {
	page,
	admin,
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

	// Act.
	await admin.visitAdminPage( 'edit.php', 'post_type=product' );

	// Assert: the notice div is not rendered at all when clearance products exist.
	await expect(
		page.locator( '.wc-clearance-onboarding-notice' )
	).not.toBeVisible();

	// Cleanup.
	await requestUtils.rest( {
		method: 'DELETE',
		path: `/wc/v3/products/${ product.id }`,
		data: { force: true },
	} );
} );

test( 'notice does not show again after being dismissed', async ( {
	page,
	admin,
} ) => {
	// Arrange: visit the product list and confirm the notice is visible.
	await admin.visitAdminPage( 'edit.php', 'post_type=product' );
	await expect(
		page.locator( '.wc-clearance-onboarding-notice' )
	).toBeVisible();

	// Act: dismiss the notice.
	await page
		.locator( '.wc-clearance-onboarding-notice .notice-dismiss' )
		.click();

	// Navigate away and back.
	await admin.visitAdminPage( 'edit.php', 'post_type=product' );

	// Assert: notice is hidden because the dismissal key is in localStorage.
	await expect(
		page.locator( '.wc-clearance-onboarding-notice' )
	).not.toBeVisible();
} );
