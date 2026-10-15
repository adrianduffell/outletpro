/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { act, fireEvent, render, screen } from '@testing-library/react';
import {
	createReduxStore,
	createRegistry,
	RegistryProvider,
} from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import type { ReactNode } from 'react';
import { Edit } from '../edit';

jest.mock( '@wordpress/editor', () => ( {
	store: { name: 'core/editor' },
} ) );

jest.mock( '@wordpress/block-editor', () => ( {
	InspectorControls: ( { children }: { children: ReactNode } ) => children,
	useBlockProps: ( props: Record< string, unknown > ) => props,
} ) );

jest.mock( '@wordpress/components', () => ( {
	PanelBody: ( {
		title,
		children,
	}: {
		title: string;
		children: ReactNode;
	} ) => (
		<section>
			<h2>{ title }</h2>
			{ children }
		</section>
	),
	ToggleControl: ( {
		label,
		checked,
		onChange,
	}: {
		label: string;
		checked: boolean;
		onChange: ( checked: boolean ) => void;
	} ) => (
		<input
			aria-label={ label }
			type="checkbox"
			checked={ checked }
			onChange={ ( event ) => onChange( event.target.checked ) }
		/>
	),
} ) );

function renderMenu(
	settings: Record< string, unknown >,
	attributes: Parameters< typeof Edit >[ 0 ][ 'attributes' ] = {}
) {
	const registry = createRegistry();
	registry.register(
		createReduxStore( editorStore.name, {
			reducer: (
				state = settings,
				action: { type: string; settings: Record< string, unknown > }
			) =>
				action.type === 'UPDATE_SETTINGS' ? action.settings : state,
			actions: {
				updateEditorSettings: (
					nextSettings: Record< string, unknown >
				) => ( {
					type: 'UPDATE_SETTINGS',
					settings: nextSettings,
				} ),
			},
			selectors: {
				getEditorSettings: ( state: Record< string, unknown > ) =>
					state,
			},
		} )
	);
	const setAttributes = jest.fn();
	const view = render(
		<RegistryProvider value={ registry }>
			<Edit
				attributes={ attributes }
				setAttributes={ setAttributes }
				__unstableLayoutClassNames="is-layout-flex"
			/>
		</RegistryProvider>
	);

	return { ...view, registry, setAttributes };
}

test( 'initializes new menus with all term IDs and includes All', () => {
	// Arrange.
	const settings = {
		outletproMenuItems: {
			categories: [
				{ id: 1, name: 'Clothing', query: 'outlet_category=clothing' },
			],
			prices: [ { id: 2, name: 'Under $25', query: 'outlet_price=25' } ],
			discounts: [
				{ id: 3, name: '30% off', query: 'outlet_discount=30' },
			],
		},
		outletproMenuPermalink: 'https://example.com/outlet/',
	};

	// Act.
	const { setAttributes } = renderMenu( settings );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledTimes( 1 );
	expect( setAttributes ).toHaveBeenCalledWith( {
		items: [ 1, 2, 3 ],
		includeAll: true,
	} );
	for ( const name of [ 'All', 'Clothing', 'Under $25', '30% off' ] ) {
		expect( screen.getByRole( 'checkbox', { name } ) ).toBeChecked();
	}
	for ( const name of [ 'Categories', 'Price tiers', 'Discount tiers' ] ) {
		expect( screen.getByRole( 'heading', { name } ) ).toBeVisible();
	}
} );

test( 'initializes when editor settings arrive after the first render', async () => {
	// Arrange.
	const { registry, setAttributes } = renderMenu( {} );

	// Act.
	await act( async () => {
		await registry.dispatch( editorStore ).updateEditorSettings( {
			outletproMenuItems: {
				categories: [
					{
						id: 1,
						name: 'Clothing',
						query: 'outlet_category=clothing',
					},
				],
			},
			outletproMenuPermalink: 'https://example.com/outlet/',
		} );
	} );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledWith( {
		items: [ 1 ],
		includeAll: true,
	} );
	expect( screen.getByRole( 'link', { name: 'Clothing' } ) ).toHaveAttribute(
		'href',
		'https://example.com/outlet/?outlet_category=clothing'
	);
} );

test( 'preserves an empty saved selection', () => {
	// Arrange.
	const settings = {
		outletproMenuItems: {
			categories: [
				{ id: 1, name: 'Clothing', query: 'outlet_category=clothing' },
			],
		},
		outletproMenuPermalink: 'https://example.com/outlet/',
	};

	// Act.
	const { setAttributes } = renderMenu( settings, {
		items: [],
		includeAll: false,
	} );

	// Assert.
	expect( setAttributes ).not.toHaveBeenCalled();
	expect( screen.queryByRole( 'link' ) ).not.toBeInTheDocument();
	expect(
		screen.getByRole( 'checkbox', { name: 'Clothing' } )
	).not.toBeChecked();
	expect( screen.getByRole( 'checkbox', { name: 'All' } ) ).not.toBeChecked();
} );

test( 'keeps a selected term opted in when its name and slug change', async () => {
	// Arrange.
	const { registry, setAttributes } = renderMenu(
		{
			outletproMenuItems: {
				categories: [
					{
						id: 1,
						name: 'Old name',
						query: 'outlet_category=old-slug',
					},
				],
			},
			outletproMenuPermalink: 'https://example.com/outlet/',
		},
		{ items: [ 1 ] }
	);

	// Act.
	await act( async () => {
		await registry.dispatch( editorStore ).updateEditorSettings( {
			outletproMenuItems: {
				categories: [
					{
						id: 1,
						name: 'New name',
						query: 'outlet_category=new-slug',
					},
					{
						id: 2,
						name: 'New category',
						query: 'outlet_category=new-category',
					},
				],
			},
			outletproMenuPermalink: 'https://example.com/outlet/',
		} );
	} );

	// Assert.
	expect( setAttributes ).not.toHaveBeenCalled();
	expect(
		screen.getByRole( 'checkbox', { name: 'New name' } )
	).toBeChecked();
	expect(
		screen.getByRole( 'checkbox', { name: 'New category' } )
	).not.toBeChecked();
	expect( screen.getByRole( 'link', { name: 'New name' } ) ).toHaveAttribute(
		'href',
		'https://example.com/outlet/?outlet_category=new-slug'
	);
	expect(
		screen.queryByRole( 'link', { name: 'Old name' } )
	).not.toBeInTheDocument();
	expect(
		screen.queryByRole( 'link', { name: 'New category' } )
	).not.toBeInTheDocument();
} );

test( 'checking an item saves only its term ID', () => {
	// Arrange.
	const { setAttributes } = renderMenu(
		{
			outletproMenuItems: {
				discounts: [
					{ id: 3, name: '30% off', query: 'outlet_discount=30' },
				],
			},
		},
		{ items: [ 1 ] }
	);

	// Act.
	fireEvent.click( screen.getByRole( 'checkbox', { name: '30% off' } ) );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledWith( { items: [ 1, 3 ] } );
} );

test( 'unchecking an item removes only its term ID', () => {
	// Arrange.
	const { setAttributes } = renderMenu(
		{
			outletproMenuItems: {
				discounts: [
					{ id: 3, name: '30% off', query: 'outlet_discount=30' },
				],
			},
		},
		{ items: [ 1, 3 ] }
	);

	// Act.
	fireEvent.click( screen.getByRole( 'checkbox', { name: '30% off' } ) );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledWith( { items: [ 1 ] } );
} );

test.each( [ true, false ] )(
	'All is stored independently of the selected term IDs (%s)',
	( includeAll ) => {
		// Arrange.
		const { setAttributes } = renderMenu(
			{
				outletproMenuPermalink: 'https://example.com/outlet/',
			},
			{ items: [ 1 ], includeAll }
		);

		// Act.
		fireEvent.click( screen.getByRole( 'checkbox', { name: 'All' } ) );

		// Assert.
		expect( setAttributes ).toHaveBeenCalledWith( {
			includeAll: ! includeAll,
		} );
	}
);

test( 'omits deleted terms from the preview without rewriting saved IDs', async () => {
	// Arrange.
	const { registry, setAttributes } = renderMenu(
		{
			outletproMenuItems: {
				discounts: [
					{ id: 3, name: '30% off', query: 'outlet_discount=30' },
				],
			},
			outletproMenuPermalink: 'https://example.com/outlet/',
		},
		{ items: [ 3 ], includeAll: true }
	);

	// Act.
	await act( async () => {
		await registry.dispatch( editorStore ).updateEditorSettings( {
			outletproMenuItems: { categories: [], prices: [], discounts: [] },
			outletproMenuPermalink: 'https://example.com/outlet/',
		} );
	} );

	// Assert.
	expect( setAttributes ).not.toHaveBeenCalled();
	expect(
		screen.queryByRole( 'link', { name: '30% off' } )
	).not.toBeInTheDocument();
	expect( screen.getByRole( 'link', { name: 'All' } ) ).toBeVisible();
} );

test( 'combines current term queries with the current permalink and preserves existing parameters', () => {
	// Arrange.
	const settings = {
		outletproMenuItems: {
			categories: [
				{
					id: 1,
					name: 'Sale',
					query: 'outlet_discount=30&outlet_category=home%20goods',
				},
			],
		},
		outletproMenuPermalink:
			'https://example.com/?page_id=42&outlet_discount=10',
	};

	// Act.
	renderMenu( settings, { items: [ 1 ] } );
	const url = new URL(
		screen.getByRole( 'link', { name: 'Sale' } ).getAttribute( 'href' ) ??
			''
	);

	// Assert.
	expect( url.origin ).toBe( 'https://example.com' );
	expect( url.searchParams.get( 'page_id' ) ).toBe( '42' );
	expect( url.searchParams.getAll( 'outlet_discount' ) ).toEqual( [ '30' ] );
	expect( url.searchParams.get( 'outlet_category' ) ).toBe( 'home goods' );
} );

test( 'previews selected terms in their saved order', () => {
	// Arrange.
	const settings = {
		outletproMenuItems: {
			categories: [
				{ id: 1, name: 'Clothing', query: 'outlet_category=clothing' },
			],
			discounts: [
				{ id: 3, name: '30% off', query: 'outlet_discount=30' },
			],
		},
	};

	// Act.
	renderMenu( settings, { items: [ 3, 1 ] } );

	// Assert.
	expect(
		screen.getAllByRole( 'link' ).map( ( link ) => link.textContent )
	).toEqual( [ '30% off', 'Clothing' ] );
} );

test( 'applies layout classes to the menu and prevents navigation in the preview', () => {
	// Arrange.
	const settings = { outletproMenuPermalink: 'https://example.com/outlet/' };

	// Act.
	const { container } = renderMenu( settings, {
		items: [],
		includeAll: true,
	} );
	const link = screen.getByRole( 'link', { name: 'All' } );

	// Assert.
	expect( container.querySelector( 'menu' ) ).toHaveClass( 'is-layout-flex' );
	expect( link.parentElement?.parentElement?.tagName ).toBe( 'MENU' );
	expect( container.querySelectorAll( 'menu' ) ).toHaveLength( 1 );
	expect( fireEvent.click( link ) ).toBe( false );
} );

test.each( [
	[ 'missing groups', {} ],
	[ 'undefined groups', { outletproMenuItems: undefined } ],
	[ 'null groups', { outletproMenuItems: null } ],
	[ 'primitive groups', { outletproMenuItems: 25 } ],
	[
		'missing categories',
		{ outletproMenuItems: { prices: [], discounts: [] } },
	],
	[
		'invalid group arrays',
		{
			outletproMenuItems: {
				categories: null,
				prices: 25,
				discounts: 'invalid',
			},
		},
	],
] )( 'renders no options or initial selection for %s', ( _name, settings ) => {
	// Arrange.
	const attributes = {};

	// Act.
	const { setAttributes } = renderMenu( settings, attributes );

	// Assert.
	expect( setAttributes ).not.toHaveBeenCalled();
	expect( screen.queryByRole( 'checkbox' ) ).not.toBeInTheDocument();
	expect( screen.queryByRole( 'link' ) ).not.toBeInTheDocument();
} );

test.each( [ undefined, null, 25 ] )(
	'uses the current document for an unavailable permalink (%s)',
	( permalink ) => {
		// Arrange.
		const settings = { outletproMenuPermalink: permalink };

		// Act.
		renderMenu( settings, { items: [], includeAll: true } );

		// Assert.
		expect( screen.getByRole( 'link', { name: 'All' } ) ).toHaveAttribute(
			'href',
			window.location.href
		);
	}
);
