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

const mockUseEntityRecords = jest.fn();
jest.mock( '@wordpress/core-data', () => ( {
	useEntityRecords: ( ...args: unknown[] ) => mockUseEntityRecords( ...args ),
} ) );

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
	attributes: Parameters< typeof Edit >[ 0 ][ 'attributes' ] = {},
	categories: { id: number; name: string }[] | null = []
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
	mockUseEntityRecords.mockReturnValue( {
		records: categories,
		hasResolved: categories !== null,
	} );
	const setAttributes = jest.fn();
	const view = render(
		<RegistryProvider value={ registry }>
			<Edit attributes={ attributes } setAttributes={ setAttributes } />
		</RegistryProvider>
	);

	return { ...view, registry, setAttributes };
}

test( 'waits for categories before opting in', () => {
	// Arrange.
	const { registry, rerender, setAttributes } = renderMenu(
		{
			outletproPriceTiers: [ { id: 2, name: 'Under $25' } ],
			outletproDiscountTiers: [ { id: 3, name: '30% off' } ],
		},
		{},
		null
	);
	expect( setAttributes ).not.toHaveBeenCalled();

	// Act.
	mockUseEntityRecords.mockReturnValue( {
		records: [ { id: 1, name: 'Clothing' } ],
		hasResolved: true,
	} );
	rerender(
		<RegistryProvider value={ registry }>
			<Edit attributes={ {} } setAttributes={ setAttributes } />
		</RegistryProvider>
	);

	// Assert.
	expect( setAttributes ).toHaveBeenCalledTimes( 1 );
	expect( setAttributes ).toHaveBeenCalledWith( {
		items: [ 1, 2, 3 ],
		showAll: true,
	} );
} );

test.each( [
	{ items: [ 1 ], expected: [ 1, 3 ] },
	{ items: [ 1, 3 ], expected: [ 1 ] },
] )( 'toggles only the selected term ID (%j)', ( { items, expected } ) => {
	// Arrange.
	const { setAttributes } = renderMenu(
		{
			outletproDiscountTiers: [ { id: 3, name: '30% off' } ],
		},
		{ items }
	);

	// Act.
	fireEvent.click( screen.getByRole( 'checkbox', { name: '30% off' } ) );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledWith( { items: expected } );
} );

test( 'persists an empty resolved selection', () => {
	// Arrange.
	const settings = { outletproPriceTiers: [], outletproDiscountTiers: [] };

	// Act.
	const { setAttributes } = renderMenu( settings );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledTimes( 1 );
	expect( setAttributes ).toHaveBeenCalledWith( {
		items: [],
		showAll: true,
	} );
} );

test( 'preserves an empty saved selection', () => {
	// Arrange.
	const categories = [ { id: 1, name: 'Clothing' } ];

	// Act.
	const { setAttributes } = renderMenu( {}, { items: [] }, categories );

	// Assert.
	expect( setAttributes ).not.toHaveBeenCalled();
	expect( screen.queryByRole( 'link' ) ).not.toBeInTheDocument();
} );

test.each( [ true, false ] )( 'toggles All independently (%s)', ( showAll ) => {
	// Arrange.
	const { setAttributes } = renderMenu( {}, { items: [ 1 ], showAll } );

	// Act.
	fireEvent.click( screen.getByRole( 'checkbox', { name: 'All' } ) );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledWith( {
		showAll: ! showAll,
	} );
} );

test( 'previews available selections in saved order', () => {
	// Arrange.
	const settings = { outletproDiscountTiers: [ { id: 3, name: '30% off' } ] };
	const categories = [ { id: 1, name: 'Home &amp; Garden' } ];

	// Act.
	renderMenu( settings, { items: [ 3, 99, 1 ] }, categories );

	// Assert.
	expect(
		screen.getAllByRole( 'link' ).map( ( link ) => link.textContent )
	).toEqual( [ '30% off', 'Home & Garden' ] );
} );
