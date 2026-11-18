/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { act, render, renderHook, waitFor } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import {
	getBlockType,
	registerBlockType,
	unregisterBlockType,
} from '@wordpress/blocks';
import { store as coreStore } from '@wordpress/core-data';
import { createRegistry, RegistryProvider, useRegistry } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { expect, test, vi } from 'vitest';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );
vi.hoisted( () => {
	Object.defineProperty( window, 'matchMedia', {
		value: () => ( {
			matches: false,
			addListener() {},
			removeListener() {},
			addEventListener() {},
			removeEventListener() {},
		} ),
	} );
} );

function renderEditor(
	Component: () => null,
	{
		template = 'archive-product',
		isBlockTheme = true,
		outletPageId = 42,
		postId = 42,
		postType = 'page',
	}: {
		template?: string;
		isBlockTheme?: boolean;
		outletPageId?: number | null;
		postId?: number;
		postType?: string;
	} = {}
) {
	const { result } = renderHook( useRegistry );
	const registry = createRegistry( {}, result.current );
	registry.register( coreStore );
	registry.register( editorStore );
	registry.dispatch( coreStore ).addEntities( [
		{ kind: 'root', name: 'site', key: false, baseURL: '/wp/v2/settings' },
		{ kind: 'postType', name: postType, baseURL: `/wp/v2/${ postType }` },
	] );
	registry.dispatch( coreStore ).receiveEntityRecords( 'root', 'site', {
		outletpro_page_id: outletPageId,
	} );
	registry.dispatch( coreStore ).receiveEntityRecords( 'postType', postType, {
		id: postId,
		type: postType,
		slug: template,
		template,
	} );
	registry
		.dispatch( coreStore )
		.finishResolution( 'getEntityRecord', [
			'postType',
			postType,
			postId,
		] );
	registry.dispatch( 'core/editor' ).setEditedPost( postType, postId );
	registry
		.dispatch( editorStore )
		.updateEditorSettings( { outletproIsBlockTheme: isBlockTheme } );
	return render(
		<RegistryProvider value={ registry }>
			<Component />
		</RegistryProvider>
	);
}

const originalEdit = () => <p>Results count</p>;

async function loadEditor() {
	registerBlockType( 'woocommerce/product-results-count', {
		title: 'Results count',
		category: 'widgets',
		edit: originalEdit,
		save: () => null,
	} );
	const { default: PageTemplateEditor } = await import( '../index' );
	return PageTemplateEditor;
}

test( 'restores removed catalog blocks on the outlet page, including delayed cleanup', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const name = 'woocommerce/product-results-count';
	const PageTemplateEditor = await loadEditor();
	unregisterBlockType( name );

	// Act.
	const view = renderEditor( PageTemplateEditor );
	try {
		// Assert.
		await waitFor( () =>
			expect( getBlockType( name )?.edit ).toBe( originalEdit )
		);
		act( () => {
			unregisterBlockType( name );
		} );
		await waitFor( () =>
			expect( getBlockType( name )?.edit ).toBe( originalEdit )
		);
	} finally {
		view.unmount();
		unregisterBlockType( name );
	}
} );

test( 'preserves a catalog block that is already registered', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const name = 'woocommerce/product-results-count';
	const PageTemplateEditor = await loadEditor();
	unregisterBlockType( name );
	const edit = () => <p>Existing editor</p>;
	registerBlockType( name, {
		title: 'Results count',
		category: 'widgets',
		edit,
		save: () => null,
	} );

	// Act.
	const view = renderEditor( PageTemplateEditor );
	try {
		// Assert.
		expect( getBlockType( name ).edit ).toBe( edit );
	} finally {
		view.unmount();
		unregisterBlockType( name );
	}
} );

test.each( [
	{ template: 'outlet-page' },
	{ isBlockTheme: false },
	{ postId: 43 },
	{ outletPageId: null },
	{ postType: 'wp_template' },
	{ postType: 'post' },
] )( 'does not restore catalog blocks for %j', async ( settings ) => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const PageTemplateEditor = await loadEditor();
	unregisterBlockType( 'woocommerce/product-results-count' );

	// Act.
	const view = renderEditor( PageTemplateEditor, settings );
	try {
		// Assert.
		expect(
			getBlockType( 'woocommerce/product-results-count' )
		).toBeUndefined();
	} finally {
		view.unmount();
	}
} );
