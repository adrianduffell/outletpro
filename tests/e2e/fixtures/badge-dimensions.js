/**
 * Expected badge computed dimensions per theme and page context.
 *
 * Keys are WordPress theme slugs. Each entry contains the expected `fontSize`
 * and `padding` (top) for the badge on the single product page and on the
 * cart page. More themes and pages can be added to this fixture in the future.
 */
export default {
	twentytwentyfive: {
		productPage: {
			fontSize: '17.6991px',
			padding: '6.37166px',
		},
		cartPage: {
			fontSize: '11.62px',
			padding: '4.1832px',
		},
	},
};
