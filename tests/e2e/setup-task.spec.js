import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'publish clearance page setup task', async ( {
	page,
	admin,
	requestUtils,
} ) => {
	// Arrange: deactivate the plugin, clear the option, then reactivate so the
	// activation hook creates a fresh clearance page.
	await requestUtils.deactivatePlugin( 'wc-clearance' );
	await requestUtils.wpCli( 'option delete wc_clearance_page_id' );
	await requestUtils.activatePlugin( 'wc-clearance' );

	// Go to the WooCommerce home and verify the task is visible and not yet complete.
	await admin.visitAdminPage( 'admin.php', 'page=wc-admin' );
	const taskItem = page.locator( '.woocommerce-task-list__item', {
		hasText: 'Publish the clearance section page',
	} );
	await expect( taskItem ).toBeVisible();
	await expect( taskItem ).not.toHaveClass( /is-complete/ );

	// Click the task to open the page editor.
	await taskItem.click();
	await expect( page ).toHaveURL( /post\.php.*action=edit/ );

	// Publish the page in the block editor.
	await page.getByRole( 'button', { name: 'Publish', exact: true } ).click();
	// Confirm in the pre-publish panel.
	await page.getByRole( 'button', { name: 'Publish', exact: true } ).click();
	await page.waitForLoadState( 'networkidle' );

	// Return to the WooCommerce homepage.
	await admin.visitAdminPage( 'admin.php', 'page=wc-admin' );

	// Assert: the task is now marked as complete.
	await expect( taskItem ).toHaveClass( /is-complete/ );
} );
