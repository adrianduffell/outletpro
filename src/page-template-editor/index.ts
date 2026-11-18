/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	getBlockType,
	getBlockTypes,
	registerBlockType,
} from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { useEffect } from '@wordpress/element';
import useUnsignedIntegerEntityProp from '../use-unsigned-integer-entity-prop';

// Keep WooCommerce's editor definitions before its Page-editor cleanup runs.
const catalogBlocks = [
	'woocommerce/breadcrumbs',
	'woocommerce/catalog-sorting',
	'woocommerce/product-results-count',
].flatMap( ( name ) => {
	const block = getBlockType( name );
	return block ? [ block ] : [];
} );

export default function PageTemplateEditor(): null {
	const [ outletPageId ] =
		useUnsignedIntegerEntityProp( 'outletpro_page_id' );
	const { isOutletPage, blockTypes } = useSelect(
		( select ) => {
			const editor = select( editorStore );
			const settings = editor.getEditorSettings();
			return {
				isOutletPage:
					outletPageId !== undefined &&
					editor.getCurrentPostType() === 'page' &&
					editor.getCurrentPostId() === outletPageId &&
					'outletproIsBlockTheme' in settings &&
					settings.outletproIsBlockTheme === true &&
					editor.getEditedPostAttribute( 'template' ) ===
						'archive-product',
				blockTypes: getBlockTypes(),
			};
		},
		[ outletPageId ]
	);
	useEffect( () => {
		if ( ! isOutletPage ) {
			return;
		}
		catalogBlocks.forEach( ( definition ) => {
			if ( getBlockType( definition.name ) ) {
				return;
			}
			registerBlockType( definition.name, definition );
		} );
	}, [ isOutletPage, blockTypes ] );
	return null;
}
