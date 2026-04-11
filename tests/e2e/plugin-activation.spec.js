import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'plugin activation seeds clearance section data', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	await requestUtils.resetSite();
	await requestUtils.rest( {
		path: '/wp/v2/plugins/woocommerce/woocommerce',
		method: 'PUT',
		data: {
			status: 'active',
		},
	} );
	await requestUtils.rest( {
		path: '/wp/v2/plugins/wc-clearance/wc-clearance',
		method: 'PUT',
		data: {
			status: 'inactive',
		},
	} );

	// Act: activate the plugin from the WP plugins screen.
	await admin.visitAdminPage( 'plugins.php' );
	await page
		.locator( 'tr[data-slug="wc-clearance"]' )
		.getByRole( 'link', { name: 'Activate' } )
		.click();

	// Assert: plugin activation success message is shown.
	await expect( page.locator( '#message' ) ).toContainText( 'Plugin activated.' );

	// Assert: WooCommerce Status screen shows seeded Canonical term ID and Page ID.
	await admin.visitAdminPage( 'admin.php', 'page=wc-status' );
	await expect(
		page.getByTestId( 'clearance-canonical-term-id' )
	).toContainText( /\d+/ );
	await expect(
		page.getByTestId( 'clearance-page-id' )
	).toContainText( /\d+/ );
} );
