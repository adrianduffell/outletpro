import { test as setup, expect } from '@wordpress/e2e-test-utils-playwright';

const themeSlug = process.env.THEME;

// Not a real test. Configures the theme on the test site based on the THEME environment variable.
setup( 'activates theme from THEME environment variable', async ( {
	page,
	admin,
} ) => {
	setup.skip( ! themeSlug, 'THEME env variable not set' );

	await admin.visitAdminPage( 'themes.php' );
	const themeEl = page.locator( `.theme[data-slug="${ themeSlug }"]` );
	await expect( themeEl ).toBeVisible();
	const activateLink = themeEl.getByRole( 'link', { name: 'Activate' } );

	await themeEl.hover();

	if ( ! ( await activateLink.isVisible() ) ) {
		await expect( themeEl ).toHaveClass( /\bactive\b/ );
		return;
	}
	await activateLink.click();

	await page.getByText( 'New theme activated.' ).waitFor();
} );
