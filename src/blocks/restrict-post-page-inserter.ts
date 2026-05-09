import { getBlockType, unregisterBlockType } from '@wordpress/blocks';
import { select } from '@wordpress/data';

declare global {
	interface Window {
		wp?: {
			domReady: ( callback: () => void ) => void;
		};
	}
}

const SITE_EDITOR_ONLY_BLOCKS = [
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

	SITE_EDITOR_ONLY_BLOCKS.forEach( ( blockName ) => {
		if ( ! getBlockType( blockName ) ) {
			return;
		}

		unregisterBlockType( blockName );
	} );
} );
