import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'system status shows clearance section info', async ( {
	page,
	admin,
} ) => {
	await admin.visitAdminPage( 'admin.php', 'page=wc-status' );

	// Check section heading is visible.
	await expect(
		page.getByRole( 'heading', { name: 'Clearance Section' } )
	).toBeVisible();

	// Check taxonomy is registered.
	await expect(
		page.getByTestId( 'clearance-taxonomy-registered' )
	).toContainText( 'Yes' );

	// Check canonical term ID is shown (not a warning).
	await expect(
		page.getByTestId( 'clearance-canonical-term-id' )
	).not.toContainText( 'Canonical term not found' );
	await expect(
		page.getByTestId( 'clearance-canonical-term-id' )
	).toHaveText( /^\d+$/ );

	// Check total products count is shown.
	await expect( page.getByTestId( 'clearance-product-count' ) ).toBeVisible();
} );
