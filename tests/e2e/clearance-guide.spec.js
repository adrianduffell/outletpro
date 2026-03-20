import { test, expect } from '@wordpress/e2e-test-utils-playwright';

async function visitClearancePageEditor( { page, admin } ) {
	await admin.visitAdminPage( 'admin.php', 'page=wc-admin' );
	const taskItem = page.locator( '.woocommerce-task-list__item', {
		hasText: 'Publish the clearance section page',
	} );
	await taskItem.click();
	await expect( page ).toHaveURL( /post\.php.*action=edit/ );
}

test( 'shows clearance guide when editing the clearance page for the first time', async ( {
	editor,
	page,
	admin,
} ) => {
	// Arrange.
	await page.goto( '/wp-admin/post-new.php' ); // fixme: Need to be on an editor page to reset preferences.
	await editor.setPreferences( 'core/edit-post', {
		welcomeGuide: false,
	} );
	await editor.setPreferences( 'wc-clearance', {
		hasSeenClearanceGuide: false,
	} );

	// Act.
	await visitClearancePageEditor( { page, admin } );

	// Assert.
	await expect(
		page.getByLabel( 'Clearance section tour guide' )
	).toBeVisible();
} );

test( 'does not show clearance guide when it has already been seen', async ( {
	editor,
	page,
	admin,
} ) => {
	// Arrange.
	await visitClearancePageEditor( { page, admin } );
	await editor.setPreferences( 'core/edit-post', {
		welcomeGuide: false,
		fullscreenMode: false,
	} );

	// Act.
	await editor.setPreferences( 'wc-clearance', {
		hasSeenClearanceGuide: true,
	} );

	// Assert.
	await expect(
		page.getByLabel( 'Clearance section tour guide' )
	).not.toBeVisible();
} );
