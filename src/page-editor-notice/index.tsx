import apiFetch from '@wordpress/api-fetch';
import { useEffect } from '@wordpress/element';
import { dispatch, select } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';

const NOTICE_ID = 'wc-outlet-empty';

export function OutletEmptyNotice(): null {
	useEffect( () => {
		async function maybeShowNotice() {
			const currentPostId = select(
				'core/editor'
			).getCurrentPostId() as number;

			// Fetch the outlet page ID from the WP settings REST API.
			let settings: { outletpro_page_id?: number };
			try {
				settings = await apiFetch< { outletpro_page_id?: number } >( {
					path: '/wp/v2/settings',
				} );
			} catch {
				return;
			}

			if (
				! settings.outletpro_page_id ||
				currentPostId !== settings.outletpro_page_id
			) {
				return;
			}

			// Check if any outlet products exist via the REST API.
			let products: unknown[];
			try {
				products = await apiFetch< unknown[] >( {
					path: '/wc/v3/products?outletpro=true&per_page=1',
				} );
			} catch {
				return;
			}

			if ( products.length > 0 ) {
				return;
			}

			// Build the products URL relative to the current wp-admin page.
			const productsUrl = new URL( 'edit.php', window.location.href );
			productsUrl.searchParams.set( 'post_type', 'product' );

			( dispatch( 'core/notices' ) as any ).createNotice(
				'warning',
				'The store’s outlet is empty. Include products to see them on this page.',
				{
					id: NOTICE_ID,
					isDismissible: false,
					actions: [
						{
							label: 'Manage products',
							isPrimary: true,
							onClick: () => {
								window.location.href = productsUrl.href;
							},
						},
					],
				}
			);
		}

		maybeShowNotice();
	}, [] );

	return null;
}

registerPlugin( 'wc-outlet-page-editor-notice', {
	render: OutletEmptyNotice,
} );
