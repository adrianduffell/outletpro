import { test, expect } from '@wordpress/e2e-test-utils-playwright';

const themeSlug = process.env.THEME;

test( 'activates theme from THEME environment variable', async ( {
	page,
	admin,
} ) => {
	test.skip( ! themeSlug, 'THEME env variable not set' );

	// Arrange.
	await admin.visitAdminPage( 'themes.php' );
	const themeEl = page.locator( `.theme[data-slug="${ themeSlug }"]` );
	await expect( themeEl ).toBeVisible();
	const activateLink = themeEl.getByRole( 'link', { name: 'Activate' } );

	if ( ! ( await activateLink.isVisible() ) ) {
		await expect( themeEl ).toHaveClass( /\bactive\b/ );
		return;
	}

	// Act.
	await themeEl.hover();
	await activateLink.click();

	// Assert.
	await page.getByText( 'New theme activated.' ).waitFor();
} );
