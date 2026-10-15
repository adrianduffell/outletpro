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
	attributes: { items?: [ string, string ][] } = {}
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

test( 'initializes new menus with every available item tuple', () => {
	// Arrange.
	const settings = {
		outletproMenuItems: {
			categories: [ [ 'All', '' ] ],
			prices: [ [ 'Under $25', 'outlet_price=25' ] ],
			discounts: [ [ '30% off', 'outlet_discount=30' ] ],
		},
		outletproMenuPermalink: 'https://example.com/outlet/',
	};

	// Act.
	const { setAttributes } = renderMenu( settings );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledTimes( 1 );
	expect( setAttributes ).toHaveBeenCalledWith( {
		items: [
			[ 'All', '' ],
			[ 'Under $25', 'outlet_price=25' ],
			[ '30% off', 'outlet_discount=30' ],
		],
	} );
	for ( const name of [ 'All', 'Under $25', '30% off' ] ) {
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
			outletproMenuItems: { categories: [ [ 'All', '' ] ] },
			outletproMenuPermalink: 'https://example.com/outlet/',
		} );
	} );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledWith( {
		items: [ [ 'All', '' ] ],
	} );
	expect( screen.getByRole( 'link', { name: 'All' } ) ).toHaveAttribute(
		'href',
		'https://example.com/outlet/'
	);
} );

test( 'preserves an empty saved selection', () => {
	// Arrange.
	const settings = { outletproMenuItems: { categories: [ [ 'All', '' ] ] } };

	// Act.
	const { setAttributes } = renderMenu( settings, { items: [] } );

	// Assert.
	expect( setAttributes ).not.toHaveBeenCalled();
	expect( screen.queryByRole( 'link' ) ).not.toBeInTheDocument();
	expect( screen.getByRole( 'checkbox', { name: 'All' } ) ).not.toBeChecked();
} );

test( 'preserves saved names and does not select newly available items', () => {
	// Arrange.
	const settings = {
		outletproMenuItems: {
			categories: [ [ 'All', '' ] ],
			discounts: [
				[ '30% off', 'outlet_discount=30' ],
				[ '50% off', 'outlet_discount=50' ],
			],
		},
		outletproMenuPermalink: 'https://example.com/outlet/',
	};

	// Act.
	const { setAttributes } = renderMenu( settings, {
		items: [ [ 'Saved sale name', 'outlet_discount=30' ] ],
	} );

	// Assert.
	expect( setAttributes ).not.toHaveBeenCalled();
	expect(
		screen.getByRole( 'link', { name: 'Saved sale name' } )
	).toBeVisible();
	expect( screen.getByRole( 'checkbox', { name: '30% off' } ) ).toBeChecked();
	expect(
		screen.getByRole( 'checkbox', { name: '50% off' } )
	).not.toBeChecked();
	expect(
		screen.queryByRole( 'link', { name: '50% off' } )
	).not.toBeInTheDocument();
} );

test( 'checking an item adds its name and query tuple', () => {
	// Arrange.
	const settings = {
		outletproMenuItems: {
			discounts: [ [ '30% off', 'outlet_discount=30' ] ],
		},
	};
	const { setAttributes } = renderMenu( settings, {
		items: [ [ 'All', '' ] ],
	} );

	// Act.
	fireEvent.click( screen.getByRole( 'checkbox', { name: '30% off' } ) );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledWith( {
		items: [
			[ 'All', '' ],
			[ '30% off', 'outlet_discount=30' ],
		],
	} );
} );

test( 'unchecking an item removes only its matching query', () => {
	// Arrange.
	const settings = {
		outletproMenuItems: {
			discounts: [ [ '30% off', 'outlet_discount=30' ] ],
		},
	};
	const { setAttributes } = renderMenu( settings, {
		items: [
			[ 'All', '' ],
			[ 'Sale', 'outlet_discount=30' ],
		],
	} );

	// Act.
	fireEvent.click( screen.getByRole( 'checkbox', { name: '30% off' } ) );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledWith( {
		items: [ [ 'All', '' ] ],
	} );
} );

test( 'All can be selected using an empty query', () => {
	// Arrange.
	const settings = { outletproMenuItems: { categories: [ [ 'All', '' ] ] } };
	const { setAttributes } = renderMenu( settings, { items: [] } );

	// Act.
	fireEvent.click( screen.getByRole( 'checkbox', { name: 'All' } ) );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledWith( {
		items: [ [ 'All', '' ] ],
	} );
} );

test( 'combines saved queries with the current permalink and preserves existing parameters', () => {
	// Arrange.
	const settings = {
		outletproMenuPermalink:
			'https://example.com/?page_id=42&outlet_discount=10',
	};

	// Act.
	renderMenu( settings, {
		items: [
			[ 'Sale', 'outlet_discount=30&outlet_category=home%20goods' ],
		],
	} );
	const link = screen.getByRole( 'link', { name: 'Sale' } );
	const url = new URL( link.getAttribute( 'href' ) ?? '' );

	// Assert.
	expect( url.origin ).toBe( 'https://example.com' );
	expect( url.searchParams.get( 'page_id' ) ).toBe( '42' );
	expect( url.searchParams.getAll( 'outlet_discount' ) ).toEqual( [ '30' ] );
	expect( url.searchParams.get( 'outlet_category' ) ).toBe( 'home goods' );
} );

test( 'applies layout classes to the menu and prevents navigation in the preview', () => {
	// Arrange.
	const settings = { outletproMenuPermalink: 'https://example.com/outlet/' };

	// Act.
	const { container } = renderMenu( settings, { items: [ [ 'All', '' ] ] } );
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
		renderMenu( settings, { items: [ [ 'All', '' ] ] } );

		// Assert.
		expect( screen.getByRole( 'link', { name: 'All' } ) ).toHaveAttribute(
			'href',
			window.location.href
		);
	}
);
