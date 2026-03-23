import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'publish clearance page setup task', async ( {
	editor,
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange
	const settings = await requestUtils.rest( {
		method: 'GET',
		path: '/wp/v2/settings',
	} );
	await requestUtils.rest( {
		method: 'POST',
		path: `/wp/v2/pages/${ settings.wc_clearance_page_id }`,
		data: {
			status: 'draft',
		},
	} );

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

	// Act
	await editor.publishPost();

	// Assert
	await admin.visitAdminPage( 'admin.php', 'page=wc-admin' );
	await expect( taskItem ).toHaveClass( /complete/ );
} );
