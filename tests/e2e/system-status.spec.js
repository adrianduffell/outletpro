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
	const taxonomyRow = page.getByRole( 'row', {
		name: /Clearance status taxonomy registered:/,
	} );
	await expect( taxonomyRow ).toContainText( 'yes' );

	// Check canonical term ID is shown (not a warning).
	const termIdRow = page.getByRole( 'row', {
		name: /Clearance status canonical term ID:/,
	} );
	await expect( termIdRow ).not.toContainText( 'Canonical term not found' );
	await expect( termIdRow.getByRole( 'cell' ).last() ).toHaveText( /^\d+$/ );

	// Check total products count is shown.
	const productsRow = page.getByRole( 'row', {
		name: /Total products in clearance section:/,
	} );
	await expect( productsRow ).toBeVisible();
} );
