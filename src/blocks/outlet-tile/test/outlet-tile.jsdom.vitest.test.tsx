/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test, vi } from 'vitest';
import { fireEvent, render, screen, within } from '@testing-library/react';
import {
	createReduxStore,
	createRegistry,
	RegistryProvider,
} from '@wordpress/data';
import { useEntityProp, useEntityRecords } from '@wordpress/core-data';
import type { PropsWithChildren } from 'react';
import { Edit } from '../edit';
import { store as outletStore } from '../../../data';

vi.mock( '@wordpress/editor', () => ( { store: { name: 'core/editor' } } ) );
vi.mock( '@wordpress/core-data', () => ( {
	useEntityProp: vi.fn(),
	useEntityRecords: vi.fn(),
} ) );
vi.mock( '@wordpress/block-editor', () => ( {
	InspectorControls: ( { children }: PropsWithChildren ) => children,
	useBlockProps: ( props: Record< string, unknown > ) => props,
} ) );

type Attributes = Parameters< typeof Edit >[ 0 ][ 'attributes' ];

function renderTile( attributes: Attributes ) {
	const registry = createRegistry();
	registry.register( outletStore );
	registry.register(
		createReduxStore( 'core/editor', {
			reducer: () => ( {
				outletproPriceTiers: [ { id: 2, name: 'Under $25' } ],
				outletproDiscountTiers: [ { id: 3, name: '50% off' } ],
			} ),
			selectors: { getEditorSettings: ( state: object ) => state },
		} )
	);
	vi.mocked( useEntityRecords, { partial: true } ).mockReturnValue( {
		records: [ { id: 1, name: 'Clothing &amp; Accessories' } ],
	} );
	vi.mocked( useEntityProp ).mockReturnValue( [
		'Sale &amp; clearance',
		vi.fn(),
		null,
	] );
	const setAttributes = vi.fn();
	const view = render(
		<RegistryProvider value={ registry }>
			<Edit attributes={ attributes } setAttributes={ setAttributes } />
		</RegistryProvider>
	);
	return { ...view, setAttributes };
}

test.each< {
	group: string;
	id: number;
	name: string;
} >( [
	{
		group: 'Product categories',
		id: 1,
		name: 'Clothing & Accessories',
	},
	{ group: 'Price tiers', id: 2, name: 'Under $25' },
	{ group: 'Discount tiers', id: 3, name: '50% off' },
] )( 'selects a term from $group', ( { group, id, name } ) => {
	// Arrange.
	const { setAttributes } = renderTile( {
		category: 0,
		displayOutletBadgeLabel: true,
	} );
	const option = within(
		screen.getByRole( 'group', { name: group } )
	).getByRole( 'option', { name } );

	// Act.
	fireEvent.change( screen.getByRole( 'combobox', { name: 'Link to' } ), {
		target: { value: option.getAttribute( 'value' ) },
	} );

	// Assert.
	expect( setAttributes ).toHaveBeenCalledWith( { category: id } );
} );

test( 'previews the selected term label', () => {
	// Arrange.
	const attributes: Attributes = {
		category: 1,
		displayOutletBadgeLabel: true,
	};

	// Act.
	renderTile( attributes );

	// Assert.
	expect( screen.getByRole( 'link' ) ).toHaveTextContent(
		'Clothing & Accessories'
	);
	expect( screen.getByRole( 'link' ) ).toHaveTextContent(
		'Sale & clearance'
	);
} );

test( 'toggles the badge label', () => {
	// Arrange.
	const { setAttributes } = renderTile( {
		category: 1,
		displayOutletBadgeLabel: true,
	} );

	// Act.
	fireEvent.click(
		screen.getByRole( 'checkbox', { name: 'Display outlet badge label' } )
	);

	// Assert.
	expect( setAttributes ).toHaveBeenCalledWith( {
		displayOutletBadgeLabel: false,
	} );
} );

test( 'omits the disabled badge label from the preview', () => {
	// Arrange.
	const attributes: Attributes = {
		category: 2,
		displayOutletBadgeLabel: false,
	};

	// Act.
	renderTile( attributes );

	// Assert.
	expect( screen.getByRole( 'link' ) ).toHaveTextContent( 'Under $25' );
	expect( screen.queryByText( 'Sale & clearance' ) ).not.toBeInTheDocument();
} );

test.each< {
	style: Attributes[ 'style' ];
	expectedDecoration: string;
} >( [
	{ style: undefined, expectedDecoration: 'none' },
	{ style: { typography: {} }, expectedDecoration: 'none' },
	{
		style: { typography: { textDecoration: 'underline' } },
		expectedDecoration: 'underline',
	},
	{
		style: { typography: { textDecoration: 'line-through' } },
		expectedDecoration: 'line-through',
	},
] )(
	'previews text decoration as $expectedDecoration',
	( { style, expectedDecoration } ) => {
		// Arrange.
		const attributes: Attributes = {
			category: 1,
			displayOutletBadgeLabel: true,
			style,
		};

		// Act.
		renderTile( attributes );

		// Assert.
		expect( screen.getByRole( 'link' ) ).toHaveStyle( {
			textDecoration: expectedDecoration,
		} );
	}
);
