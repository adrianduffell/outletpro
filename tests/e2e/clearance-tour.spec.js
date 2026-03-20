import { test, expect } from '@wordpress/e2e-test-utils-playwright';

async function visitClearancePageEditor( { page, admin } ) {
	await admin.visitAdminPage( 'admin.php', 'page=wc-admin' );
	const taskItem = page.locator( '.woocommerce-task-list__item', {
		hasText: 'Publish the clearance section page',
	} );
	await taskItem.click();
	await expect( page ).toHaveURL( /post\.php.*action=edit/ );
}

test( 'shows clearance tour when editing the clearance page for the first time', async ( {
	editor,
	page,
	admin,
} ) => {
	// Arrange.
	await page.goto( '/wp-admin/post-new.php' ); // Need to be on an editor page to set preferences.
	await editor.setPreferences( 'core/edit-post', {
		welcomeGuide: false,
		fullscreenMode: false,
	} );
	await editor.setPreferences( 'wc-clearance', {
		hasSeenClearanceTour: false,
	} );

	// Act.
	await visitClearancePageEditor( { page, admin } );

	// Assert.
	await expect(
		page.locator( '.driver-popover.wc-clearance-tour' )
	).toBeVisible();
} );

test( 'does not show clearance tour when it has already been seen', async ( {
	editor,
	page,
	admin,
} ) => {
	// Arrange.
	await page.goto( '/wp-admin/post-new.php' ); // Need to be on an editor page to set preferences.
	await editor.setPreferences( 'wc-clearance', {
		hasSeenClearanceTour: true,
	} );

	// Act.
	await visitClearancePageEditor( { page, admin } );

	// Assert.
	await expect(
		page.locator( '.driver-popover.wc-clearance-tour' )
	).not.toBeVisible();
} );
