/**
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'plugin activation seeds outlet settings', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange.
	await requestUtils.rest( {
		path: '/wp/v2/plugins/outletpro/outletpro',
		method: 'PUT',
		data: {
			status: 'inactive',
		},
	} );

	// Act: activate the plugin from the WP plugins screen.
	await admin.visitAdminPage( 'plugins.php' );
	await page
		.locator( 'tr[data-slug="outlet-pro"]' )
		.getByRole( 'link', { name: 'Activate' } )
		.click();

	// Assert: plugin activation success message is shown.
	await expect( page.getByText( 'Plugin activated' ) ).toBeVisible();

	// Assert: WooCommerce Status screen shows seeded Canonical term ID and Page ID.
	await admin.visitAdminPage( 'admin.php', 'page=wc-status' );
	await expect(
		page.getByTestId( 'outlet-canonical-term-id' )
	).toContainText( /\d+/ );
	await expect( page.getByTestId( 'outlet-page-id' ) ).toContainText( /\d+/ );
} );
