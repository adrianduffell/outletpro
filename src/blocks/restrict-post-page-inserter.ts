import { unregisterBlockType } from '@wordpress/blocks';
import { select } from '@wordpress/data';

declare global {
	interface Window {
		wp?: {
			domReady: ( callback: () => void ) => void;
		};
	}
}

const BLOCKS_RESTRICTED_TO_SITE_EDITOR = [
	'wc-clearance/clearance-badge',
	'wc-clearance/clearance-message',
];

const isPostOrPage = ( postType?: string ): boolean =>
	postType === 'post' || postType === 'page';

window.wp?.domReady( () => {
	const postType = select( 'core/editor' )?.getCurrentPostType?.();

	if ( ! isPostOrPage( postType ) ) {
		return;
	}

	BLOCKS_RESTRICTED_TO_SITE_EDITOR.forEach( ( blockName ) => {
		unregisterBlockType( blockName );
	} );
} );
