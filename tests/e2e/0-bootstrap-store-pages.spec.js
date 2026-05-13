import { test as setup, expect } from '@wordpress/e2e-test-utils-playwright';

const storeContent = process.env.STORE_CONTENT;

/**
 * Fetches the cart and checkout page IDs from the WooCommerce advanced settings.
 *
 * @param {Object} requestUtils - Playwright request utilities.
 * @return {Promise<{cartPageId: number|null, checkoutPageId: number|null}>} Cart and checkout page IDs.
 */
async function getStorePageIds( requestUtils ) {
	const settings = await requestUtils.rest( {
		method: 'GET',
		path: '/wc/v3/settings/advanced',
	} );

	const cartSetting = settings.find(
		( s ) => s.id === 'woocommerce_cart_page_id'
	);
	const checkoutSetting = settings.find(
		( s ) => s.id === 'woocommerce_checkout_page_id'
	);

	return {
		cartPageId: cartSetting?.value ? Number( cartSetting.value ) : null,
		checkoutPageId: checkoutSetting?.value
			? Number( checkoutSetting.value )
			: null,
	};
}

/**
 * Deletes a page by ID via the WordPress REST API.
 *
 * Ignores not-found errors so bootstrap can be rerun safely when settings
 * still reference pages that were already removed.
 *
 * @param {Object} requestUtils - Playwright request utilities.
 * @param {number} pageId       - The ID of the page to delete.
 */
async function deletePage( requestUtils, pageId ) {
	try {
		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wp/v2/pages/${ pageId }`,
			params: {
				force: true,
			},
		} );
	} catch ( error ) {
		// requestUtils.rest() may surface the HTTP status in different places
		// depending on the error type (@wordpress/api-fetch vs raw fetch vs WP REST error body).
		const statusCode =
			error?.status ??
			error?.response?.status ??
			error?.data?.status ??
			error?.code;

		if ( statusCode === 404 || statusCode === 'rest_post_invalid_id' ) {
			return;
		}

		throw error;
	}
}

setup( 'install store pages with blocks', async ( { requestUtils } ) => {
	setup.skip(
		storeContent !== 'blocks',
		'STORE_CONTENT is not set to blocks'
	);

	const { cartPageId, checkoutPageId } =
		await getStorePageIds( requestUtils );

	if ( cartPageId ) {
		await deletePage( requestUtils, cartPageId );
	}

	if ( checkoutPageId ) {
		await deletePage( requestUtils, checkoutPageId );
	}

	await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/system_status/tools/install_pages',
	} );

	const { cartPageId: newCartPageId, checkoutPageId: newCheckoutPageId } =
		await getStorePageIds( requestUtils );

	expect( newCartPageId ).toBeGreaterThan( 0 );
	expect( newCheckoutPageId ).toBeGreaterThan( 0 );
} );

setup( 'install store pages with shortcodes', async ( { requestUtils } ) => {
	setup.skip(
		storeContent !== 'shortcodes',
		'STORE_CONTENT is not set to shortcodes'
	);

	const { cartPageId, checkoutPageId } =
		await getStorePageIds( requestUtils );

	if ( cartPageId ) {
		await deletePage( requestUtils, cartPageId );
	}

	if ( checkoutPageId ) {
		await deletePage( requestUtils, checkoutPageId );
	}

	const cartPage = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: {
			title: 'Cart',
			content:
				'<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->',
			status: 'publish',
		},
	} );

	const checkoutPage = await requestUtils.rest( {
		method: 'POST',
		path: '/wp/v2/pages',
		data: {
			title: 'Checkout',
			content:
				'<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->',
			status: 'publish',
		},
	} );

	await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/settings/advanced/woocommerce_cart_page_id',
		data: {
			value: String( cartPage.id ),
		},
	} );

	await requestUtils.rest( {
		method: 'POST',
		path: '/wc/v3/settings/advanced/woocommerce_checkout_page_id',
		data: {
			value: String( checkoutPage.id ),
		},
	} );

	expect( cartPage.status ).toBe( 'publish' );
	expect( checkoutPage.status ).toBe( 'publish' );

	const {
		cartPageId: updatedCartPageId,
		checkoutPageId: updatedCheckoutPageId,
	} = await getStorePageIds( requestUtils );

	expect( updatedCartPageId ).toBe( cartPage.id );
	expect( updatedCheckoutPageId ).toBe( checkoutPage.id );
} );
