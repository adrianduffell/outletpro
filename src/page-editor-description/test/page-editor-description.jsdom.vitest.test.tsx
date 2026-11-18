/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { act, render, renderHook, waitFor } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { store as coreStore } from '@wordpress/core-data';
import { createRegistry, RegistryProvider, useRegistry } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { expect, test, vi } from 'vitest';
import OutletPageEditorDescription from '../index';

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

const sidebar = (
	<section className="editor-post-summary">
		<div className="editor-post-card-panel__header">Outlet</div>
		<button>Set featured image</button>
		<div className="editor-post-panel__row">
			<span>Template</span>
			<div className="editor-post-panel__row-control">
				<button aria-label="Template options">Pages</button>
			</div>
		</div>
		<div>Outlet settings</div>
	</section>
);
const description = 'Displays the store’s outlet products.';

function renderDescription(
	settings: Record< string, unknown > = {},
	template = ''
) {
	const { result } = renderHook( useRegistry );
	const registry = createRegistry( {}, result.current );
	registry.register( coreStore );
	registry.register( editorStore );
	registry.dispatch( coreStore ).addEntities( [
		{ kind: 'root', name: 'site', key: false, baseURL: '/wp/v2/settings' },
		{ kind: 'postType', name: 'page', baseURL: '/wp/v2/pages' },
	] );
	registry
		.dispatch( coreStore )
		.receiveEntityRecords( 'root', 'site', { outletpro_page_id: 42 } );
	registry.dispatch( coreStore ).receiveEntityRecords( 'postType', 'page', {
		id: 42,
		type: 'page',
		status: 'publish',
		template,
	} );
	registry.dispatch( 'core/editor' ).setEditedPost( 'page', 42 );
	registry.dispatch( editorStore ).updateEditorSettings( settings );
	const view = render(
		<RegistryProvider value={ registry }>
			<OutletPageEditorDescription />
		</RegistryProvider>
	);
	return { registry, view };
}

test( 'places the description beneath the title and preserves native controls', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const panel = render( sidebar );

	// Act.
	renderDescription();

	// Assert.
	const text = await panel.findByText( description );
	expect( text.parentElement?.previousElementSibling ).toBe(
		panel.getByText( 'Outlet' )
	);
	expect(
		panel.getByRole( 'button', { name: 'Template options' } )
	).toHaveTextContent( 'Pages' );
	expect(
		panel.getByRole( 'button', { name: 'Set featured image' } )
	).toBeVisible();
	expect( panel.getByText( 'Outlet settings' ) ).toBeVisible();
} );

test.each( [
	{ outletproIsBlockTheme: false },
	{ outletproIsBlockTheme: true },
] )( 'shows the description for %j', async ( settings ) => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const panel = render( sidebar );

	// Act.
	renderDescription( settings );

	// Assert.
	expect( await panel.findByText( description ) ).toBeVisible();
} );

test( 'hides the description for legacy outlet pages', () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const panel = render( sidebar );

	// Act.
	renderDescription( {}, 'outlet-page' );

	// Assert.
	expect( panel.queryByText( description ) ).toBeNull();
	expect(
		panel.container.querySelector( '.outletpro-page-description' )
	).toBeNull();
} );

test( 'updates the description when the page template changes', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const panel = render( sidebar );
	const { registry } = renderDescription();
	await panel.findByText( description );

	// Act.
	act( () => {
		registry
			.dispatch( editorStore )
			.editPost( { template: 'outlet-page' } );
	} );

	// Assert.
	expect( panel.queryByText( description ) ).toBeNull();

	// Act.
	act( () => {
		registry.dispatch( editorStore ).editPost( { template: '' } );
	} );

	// Assert.
	expect( await panel.findByText( description ) ).toBeVisible();
} );

test( 'shows the description for a draft outlet page', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const panel = render( sidebar );
	const { registry } = renderDescription();

	// Act.
	act( () => {
		registry.dispatch( editorStore ).editPost( { status: 'draft' } );
	} );

	// Assert.
	expect( await panel.findByText( description ) ).toBeVisible();
} );

test.each( [
	{ postType: 'page', postId: 43, outletPageId: 42 },
	{ postType: 'product', postId: 42, outletPageId: 42 },
	{ postType: 'page', postId: 42, outletPageId: null },
] )(
	'removes the description outside the configured outlet page: %j',
	async ( state ) => {
		// Arrange.
		vi.mocked( apiFetch ).mockResolvedValue( {} );
		const panel = render( sidebar );
		const { registry } = renderDescription();
		await panel.findByText( description );

		// Act.
		act( () => {
			registry
				.dispatch( 'core/editor' )
				.setEditedPost( state.postType, state.postId );
			registry
				.dispatch( coreStore )
				.receiveEntityRecords( 'root', 'site', {
					outletpro_page_id: state.outletPageId,
				} );
		} );

		// Assert.
		expect( panel.queryByText( description ) ).toBeNull();
		expect(
			panel.container.querySelector( '.outletpro-page-description' )
		).toBeNull();
	}
);

test( 'attaches to delayed and rebuilt sidebars without duplicating content', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	renderDescription();
	const panel = render( <></> );

	// Act.
	panel.rerender( sidebar );
	await panel.findByText( description );
	panel.rerender( <></> );
	panel.rerender( sidebar );
	await panel.findByText( description );
	panel.rerender(
		<section className="editor-post-summary">
			<div className="editor-post-card-panel__header">Clearance</div>
		</section>
	);

	// Assert.
	await waitFor( () => {
		expect( panel.getAllByText( description ) ).toHaveLength( 1 );
		expect(
			panel.getByText( description ).parentElement?.previousElementSibling
		).toBe( panel.getByText( 'Clearance' ) );
	} );
} );

test( 'unmounting removes the portal and stops attaching to sidebars', async () => {
	// Arrange.
	vi.mocked( apiFetch ).mockResolvedValue( {} );
	const panel = render( sidebar );
	const { view } = renderDescription();
	await panel.findByText( description );

	// Act.
	view.unmount();
	panel.rerender( <></> );
	await act( async () => panel.rerender( sidebar ) );

	// Assert.
	expect( panel.queryByText( description ) ).toBeNull();
	expect(
		panel.container.querySelector( '.outletpro-page-description' )
	).toBeNull();
	expect(
		panel.getByRole( 'button', { name: 'Template options' } )
	).toHaveTextContent( 'Pages' );
} );
