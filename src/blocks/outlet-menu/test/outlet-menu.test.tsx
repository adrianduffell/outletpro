/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render, screen } from '@testing-library/react';
import {
	createReduxStore,
	createRegistry,
	RegistryProvider,
} from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import type { PropsWithChildren } from 'react';
import { Edit } from '../edit';

jest.mock( '@wordpress/editor', () => ( {
	store: { name: 'core/editor' },
} ) );

jest.mock( '@wordpress/block-editor', () => ( {
	InspectorControls: ( { children }: PropsWithChildren ) => children,
	useBlockProps: ( props: Record< string, unknown > ) => props,
} ) );

jest.mock( '@wordpress/components', () => ( {
	PanelBody: ( { children }: PropsWithChildren ) => children,
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
			reducer: () => settings,
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

	return { ...view, setAttributes };
}

test( 'opts all available term IDs into a new menu', () => {
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
	};

	// Act.
	const { setAttributes } = renderMenu( settings );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledTimes( 1 );
	expect( setAttributes ).toHaveBeenCalledWith( {
		items: [ 1, 2, 3 ],
		includeAll: true,
	} );
} );

test.each( [
	{ items: [ 1 ], expected: [ 1, 3 ] },
	{ items: [ 1, 3 ], expected: [ 1 ] },
] )( 'toggles only the selected term ID (%j)', ( { items, expected } ) => {
	// Arrange.
	const { setAttributes } = renderMenu(
		{
			outletproMenuItems: {
				discounts: [
					{ id: 3, name: '30% off', query: 'outlet_discount=30' },
				],
			},
		},
		{ items }
	);
	const toggle = screen.getByRole( 'checkbox', { name: '30% off' } );

	// Act.
	fireEvent.click( toggle );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledTimes( 1 );
	expect( setAttributes ).toHaveBeenCalledWith( {
		items: expected,
	} );
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
	const { setAttributes } = renderMenu( settings, { items: [] } );

	// Assert.
	expect( setAttributes ).not.toHaveBeenCalled();
	expect( screen.queryByRole( 'link' ) ).not.toBeInTheDocument();
} );

test.each( [ true, false ] )(
	'toggles All independently (%s)',
	( includeAll ) => {
		// Arrange.
		const { setAttributes } = renderMenu(
			{ outletproMenuPermalink: 'https://example.com/outlet/' },
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

test( 'previews current term data in saved order and skips deleted IDs', () => {
	// Arrange.
	const settings = {
		outletproMenuItems: {
			categories: [
				{ id: 1, name: 'Renamed', query: 'outlet_category=new-slug' },
			],
			discounts: [
				{ id: 3, name: '30% off', query: 'outlet_discount=30' },
			],
		},
		outletproMenuPermalink: 'https://example.com/?page_id=42',
	};

	// Act.
	const { setAttributes } = renderMenu( settings, { items: [ 3, 99, 1 ] } );
	const links = screen.getAllByRole( 'link' );

	// Assert.
	expect( setAttributes ).not.toHaveBeenCalled();
	expect( links.map( ( link ) => link.textContent ) ).toEqual( [
		'30% off',
		'Renamed',
	] );
	expect( links[ 0 ] ).toHaveAttribute(
		'href',
		'https://example.com/?page_id=42&outlet_discount=30'
	);
	expect( links[ 1 ] ).toHaveAttribute(
		'href',
		'https://example.com/?page_id=42&outlet_category=new-slug'
	);
} );
