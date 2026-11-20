/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render, within } from '@testing-library/react';
import { expect, test } from 'vitest';
import TierList from '../TierList';
import type { OutletTier } from '../types';

test( 'lists configured tier labels and leaves the dummy add button inactive', () => {
	// Arrange.
	const tiers = [
		{ id: 15, name: '45% off' },
		{ id: 18, name: '80% off &amp; more' },
	];
	const view = render( <TierList kind="discount" tiers={ tiers } /> );
	const list = view.getByRole( 'list', { name: 'Discount levels' } );

	// Act.
	fireEvent.click(
		view.getByRole( 'button', { name: 'Add discount level' } )
	);

	// Assert.
	expect(
		view.getByRole( 'heading', { name: 'Discount levels', level: 2 } )
	).toBeVisible();
	expect(
		within( list )
			.getAllByRole( 'listitem' )
			.map( ( item ) => item.textContent )
	).toEqual( [ '45% off', '80% off & more' ] );
	expect( view.queryByRole( 'textbox' ) ).not.toBeInTheDocument();
} );

test( 'shows an empty state without inventing discount levels', () => {
	// Arrange.
	const tiers: OutletTier[] = [];

	// Act.
	const view = render( <TierList kind="discount" tiers={ tiers } /> );

	// Assert.
	expect( view.getByText( 'No discount levels configured.' ) ).toBeVisible();
	expect( view.queryByRole( 'listitem' ) ).not.toBeInTheDocument();
	expect(
		view.getByRole( 'button', { name: 'Add discount level' } )
	).toBeEnabled();
} );

test( 'reports unavailable tier data separately from an empty configuration', () => {
	// Arrange.
	const tiers = null;

	// Act.
	const view = render( <TierList kind="discount" tiers={ tiers } /> );

	// Assert.
	expect(
		view.getByText(
			'Discount levels could not be loaded. Refresh the page to try again.'
		)
	).toBeVisible();
	expect(
		view.queryByText( 'No discount levels configured.' )
	).not.toBeInTheDocument();
} );

test( 'lists real price band labels with a separate dummy add button', () => {
	// Arrange.
	const tiers = [
		{ id: 21, name: 'Under £35' },
		{ id: 22, name: 'Under £120' },
	];
	const view = render( <TierList kind="price" tiers={ tiers } /> );
	const list = view.getByRole( 'list', { name: 'Price bands' } );

	// Act.
	fireEvent.click( view.getByRole( 'button', { name: 'Add price band' } ) );

	// Assert.
	expect(
		view.getByRole( 'heading', { name: 'Price bands', level: 2 } )
	).toBeVisible();
	expect(
		within( list )
			.getAllByRole( 'listitem' )
			.map( ( item ) => item.textContent )
	).toEqual( [ 'Under £35', 'Under £120' ] );
	expect( view.queryByRole( 'textbox' ) ).not.toBeInTheDocument();
} );
