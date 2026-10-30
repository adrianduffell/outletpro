/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { useEffect } from '@wordpress/element';
import {
	useDispatch,
	useSelect,
	select,
	subscribe,
	resolveSelect,
} from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { PluginPostStatusInfo, store as editorStore } from '@wordpress/editor';
import { getBlockType, registerBlockType } from '@wordpress/blocks';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { getCatalogEditorSettings, getCatalogEditorUrl } from './settings';

const catalogBlocks = [
	'woocommerce/breadcrumbs',
	'woocommerce/catalog-sorting',
	'woocommerce/product-results-count',
	'woocommerce/legacy-template',
];

const blockDefinitions = new Map();
for ( const name of catalogBlocks ) {
	const definition = getBlockType( name );
	if ( definition ) {
		blockDefinitions.set( name, definition );
	}
}

// WooCommerce unregisters these blocks outside its assigned Shop Page. Capture
// their real definitions so the Outlet can preview the same catalog on 11.1+.
addFilter(
	'blocks.registerBlockType',
	'outletpro/catalog-blocks',
	( settings, name ) => {
		if ( catalogBlocks.includes( name ) ) {
			blockDefinitions.set( name, settings );
		}
		return settings;
	}
);

let restoringBlocks = false;
subscribe( () => {
	if ( restoringBlocks ) {
		return;
	}
	const settings = getCatalogEditorSettings(
		select( editorStore )?.getEditorSettings() ?? {}
	);
	if ( ! settings.isCatalog ) {
		return;
	}
	if ( ! settings.isBlockTheme ) {
		return;
	}
	restoringBlocks = true;
	try {
		for ( const [ name, definition ] of blockDefinitions ) {
			if ( ! getBlockType( name ) ) {
				registerBlockType( name, definition );
			}
		}
	} finally {
		restoringBlocks = false;
	}
} );

export default function CatalogEditor(): JSX.Element | null {
	const { postId, settings } = useSelect( ( selectStore ) => {
		const editor = selectStore( editorStore );
		return {
			postId: editor.getCurrentPostId(),
			settings: getCatalogEditorSettings( editor.getEditorSettings() ),
		};
	}, [] );
	const { setRenderingMode } = useDispatch( editorStore );
	const { receiveEntityRecords } = useDispatch( coreStore );
	const { isCatalog, isBlockTheme } = settings;
	const { templateId, previewContent } = settings;

	useEffect( () => {
		if ( ! isCatalog ) {
			return;
		}
		if ( ! isBlockTheme ) {
			return;
		}
		if ( ! settings.supportsTemplateMode ) {
			return;
		}
		const previousMode = select( editorStore ).getRenderingMode();
		setRenderingMode( 'template-locked' );
		return () => {
			setRenderingMode( previousMode );
		};
	}, [
		isCatalog,
		isBlockTheme,
		postId,
		settings.supportsTemplateMode,
		setRenderingMode,
	] );

	// Keep preview-only title/query adjustments in this Page editor's entity cache.
	// The saved template remains unchanged and Site Editor opens its real record.
	useEffect( () => {
		if ( ! isCatalog ) {
			return;
		}
		if ( ! isBlockTheme ) {
			return;
		}
		if ( templateId === null ) {
			return;
		}
		if ( previewContent === null ) {
			return;
		}
		let active = true;
		const loadPreview = async () => {
			try {
				const record = await resolveSelect( coreStore ).getEntityRecord(
					'postType',
					'wp_template',
					templateId
				);
				if ( ! active ) {
					return;
				}
				if ( ! record ) {
					return;
				}
				receiveEntityRecords( 'postType', 'wp_template', [
					{ ...record, content: { raw: previewContent } },
				] );
			} catch {
				// eslint-disable-next-line no-console -- Report a failed editor REST boundary while keeping normal Page editing available.
				console.error( 'Outlet catalog preview could not be loaded.' );
			}
		};
		loadPreview();
		return () => {
			active = false;
		};
	}, [
		isCatalog,
		isBlockTheme,
		templateId,
		previewContent,
		receiveEntityRecords,
	] );

	if ( ! isCatalog ) {
		return null;
	}
	if ( ! isBlockTheme ) {
		return null;
	}
	if ( settings.templateId === null ) {
		return null;
	}
	return (
		<PluginPostStatusInfo>
			<p>
				{ __(
					'Edit navigation and promotional content here. The product layout comes from the saved Outlet catalog template.',
					'outletpro'
				) }{ ' ' }
				<a
					href={ getCatalogEditorUrl(
						window.location.href,
						settings.templateId
					) }
				>
					{ __( 'Edit catalog layout', 'outletpro' ) }
				</a>
			</p>
		</PluginPostStatusInfo>
	);
}
