import { useEffect } from '@wordpress/element';
import { dispatch } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';

declare global {
	interface Window {
		wcClearanceEditorData?: {
			noProductsNotice: boolean;
		};
	}
}

const NOTICE_ID = 'wc-clearance-no-products';

export function PageEditorNotice(): null {
	// The notice data is injected server-side via wp_localize_script when the page
	// loads and does not change during the editor session. Running once on mount is
	// correct and intentional.
	useEffect( () => {
		const data = window.wcClearanceEditorData;

		if ( ! data?.noProductsNotice ) {
			return;
		}

		// Build the products URL relative to the current wp-admin page.
		const productsUrl = new URL( 'edit.php', window.location.href );
		productsUrl.searchParams.set( 'post_type', 'product' );

		dispatch( 'core/notices' ).createNotice(
			'warning',
			'The clearance section has no products. Include products to display them on this page.',
			{
				id: NOTICE_ID,
				isDismissible: false,
				actions: [
					{
						label: 'Learn how',
						url: productsUrl.href,
					},
				],
			}
		);
	}, [] );

	return null;
}

registerPlugin( 'wc-clearance-page-editor-notice', {
	render: PageEditorNotice,
} );
