import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'publish clearance page setup task', async ( {
	editor,
	page,
	admin,
} ) => {
	// Todo: Assumes the plugin activation routine ran and
	// page is in a draft state . A possible iteration is to
	// run the activation routine and this in an isolated project.

	// Arrange
	await admin.visitAdminPage( 'admin.php', 'page=wc-admin' );
	const taskItem = page.locator( '.woocommerce-task-list__item', {
		hasText: 'Publish the clearance section page',
	} );
	await expect( taskItem ).toBeVisible();
	await expect( taskItem ).not.toHaveClass( /is-complete/ );

	await taskItem.click();
	await expect( page ).toHaveURL( /post\.php.*action=edit/ );

	await editor.setPreferences( 'core/edit-post', {
		welcomeGuide: false,
		fullscreenMode: false,
	} );
	await editor.setPreferences( 'wc-clearance', {
		hasSeenClearanceTour: true,
	} );

	// Act
	await editor.publishPost();

	// Assert
	await admin.visitAdminPage( 'admin.php', 'page=wc-admin' );
	await expect( taskItem ).toHaveClass( /complete/ );
} );
