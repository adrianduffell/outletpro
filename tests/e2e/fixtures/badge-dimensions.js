/**
 * Expected badge computed dimensions per theme, viewport, and page context.
 *
 * Top-level keys are WordPress theme slugs. Each theme entry is keyed by
 * viewport size (`{width}x{height}`). Each viewport entry contains the
 * expected `fontSize` and `padding` (top) for the badge on the single product
 * page, cart page, and checkout page.
 *
 * `cartPage` and `checkoutPage` use the same CSS source (cart.css) but are
 * kept as separate keys so dimensions can be tuned independently if themes
 * render the two pages at different sizes.
 *
 * Add a new top-level key for each additional theme, and a new viewport key
 * for each viewport size to exercise.
 */
export default {
	twentytwentyfive: {
		'1280x720': {
			productPage: {
				fontSize: '18.0635px',
				padding: '6.50284px',
			},
			cartPage: {
				fontSize: '11.62px',
				padding: '4.1832px',
			},
			checkoutPage: {
				fontSize: '11.62px',
				padding: '4.1832px',
			},
		},
	},
};
