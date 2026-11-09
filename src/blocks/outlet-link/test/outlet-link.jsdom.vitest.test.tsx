/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test, vi } from 'vitest';
import {
	act,
	fireEvent,
	render,
	renderHook,
	screen,
} from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { createRegistry, RegistryProvider, useRegistry } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import {
	BlockEditorProvider,
	BlockList,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { SlotFillProvider } from '@wordpress/components';
import {
	createBlock,
	parse,
	registerBlockType,
	serialize,
} from '@wordpress/blocks';
import { Edit } from '../edit';
import metadata from '../block.json';

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
registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
	save: () => null,
} );

function renderLinks( attributes: { text?: string }[] ) {
	const { result } = renderHook( useRegistry );
	const registry = createRegistry( {}, result.current );
	registry.register( coreStore );
	registry.register( blockEditorStore );
	registry.dispatch( coreStore ).addEntities( [
		{ kind: 'root', name: 'site', key: false, baseURL: '/wp/v2/settings' },
		{ kind: 'postType', name: 'page', baseURL: '/wp/v2/pages' },
	] );
	registry
		.dispatch( coreStore )
		.receiveEntityRecords( 'root', 'site', { outletpro_page_id: 42 } );
	registry.dispatch( coreStore ).receiveEntityRecords( 'postType', 'page', {
		id: 42,
		link: 'https://example.com/outlet/',
		title: { rendered: '<em>Last Chance</em> &amp; Final Sale' },
	} );
	const blocks = attributes.map( ( attrs ) =>
		createBlock( metadata.name, attrs )
	);
	render(
		<RegistryProvider value={ registry }>
			<SlotFillProvider>
				<BlockEditorProvider value={ blocks } useSubRegistry={ false }>
					<BlockList />
				</BlockEditorProvider>
			</SlotFillProvider>
		</RegistryProvider>
	);
	act( () => {
		registry
			.dispatch( 'core/block-editor' )
			.selectBlock( blocks[ 0 ].clientId );
	} );
	return { registry, blocks };
}

test( 'saves custom text for only the selected link instance', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const { registry, blocks } = renderLinks( [ {}, {} ] );
	const [ preview ] = await screen.findAllByRole( 'link', {
		name: 'Block: Outlet Link',
	} );
	expect( preview ).toHaveAttribute( 'contenteditable', 'true' );
	expect( preview ).toHaveClass( 'wp-block-outletpro-outlet-link' );
	expect( preview ).toHaveTextContent( 'Shop all last chance & final sale' );
	expect(
		registry
			.select( 'core/block-editor' )
			.getBlockAttributes( blocks[ 0 ].clientId ).text
	).toBeUndefined();

	// Act.
	act( () => {
		preview.focus();
		preview.textContent = 'Browse <Deals> & More';
		preview.ownerDocument.defaultView
			?.getSelection()
			?.selectAllChildren( preview );
		preview.ownerDocument.defaultView?.getSelection()?.collapseToEnd();
		fireEvent.input( preview, { inputType: 'insertText' } );
	} );
	const saved = serialize(
		registry.select( 'core/block-editor' ).getBlocks()
	);

	// Assert.
	expect(
		await screen.findByText( 'Browse <Deals> & More', { selector: 'a' } )
	).toHaveAttribute( 'href', 'https://example.com/outlet/' );
	expect(
		screen.getByText( 'Shop all last chance & final sale', {
			selector: 'a',
		} )
	).toBeInTheDocument();
	expect( parse( saved )[ 0 ].attributes.text ).toBe(
		'Browse <Deals> & More'
	);
	expect(
		registry
			.select( 'core/block-editor' )
			.getBlockAttributes( blocks[ 1 ].clientId ).text
	).toBeUndefined();
} );

test( 'clearing custom text restores the current outlet page title', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const { registry } = renderLinks( [ { text: 'Browse Our Outlet' } ] );
	const preview = await screen.findByRole( 'link', {
		name: 'Block: Outlet Link',
	} );
	act( () => {
		registry
			.dispatch( coreStore )
			.receiveEntityRecords( 'postType', 'page', {
				id: 42,
				link: 'https://example.com/outlet/',
				title: { rendered: 'New Outlet' },
			} );
	} );

	// Act.
	act( () => {
		preview.focus();
		preview.textContent = '';
		preview.ownerDocument.defaultView
			?.getSelection()
			?.collapse( preview, 0 );
		fireEvent.input( preview, { inputType: 'deleteContentBackward' } );
	} );

	// Assert.
	expect(
		await screen.findByText( 'Shop all new outlet', { selector: 'a' } )
	).toHaveAttribute( 'href', 'https://example.com/outlet/' );
} );
