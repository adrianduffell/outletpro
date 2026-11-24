/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { fireEvent, render } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { store as coreStore } from '@wordpress/core-data';
import { RegistryProvider } from '@wordpress/data';
import { expect, test, vi } from 'vitest';
import OutletToggle from '../OutletToggle';
import { getProductRow } from '../presentation';
import {
	createProduct,
	createProductsRegistry,
} from '../../../data/products/test-utils/store-fixtures';
import { PRODUCT_ENTITY } from '../../../data/products/product-entity';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

test( 'stages membership in core-data without saving and removes reverted edits', () => {
	// Arrange.
	const product = createProduct();
	const registry = createProductsRegistry( [ product ] );
	const { kind, name } = PRODUCT_ENTITY;
	vi.mocked( apiFetch ).mockReset();
	const view = render(
		<RegistryProvider value={ registry }>
			<OutletToggle item={ getProductRow( product, true ) } />
		</RegistryProvider>
	);
	const toggle = view.getByRole( 'checkbox', {
		name: 'Include Hat in outlet',
	} );

	// Act.
	fireEvent.click( toggle );

	// Assert.
	expect( toggle ).toBeChecked();
	expect(
		registry
			.select( coreStore )
			.getEntityRecordEdits( kind, name, product.id )
	).toEqual( { outletpro: { is_outlet: true } } );
	expect( apiFetch ).not.toHaveBeenCalled();
	fireEvent.click( toggle );
	expect( toggle ).not.toBeChecked();
	expect(
		registry
			.select( coreStore )
			.hasEditsForEntityRecord( kind, name, product.id )
	).toBe( false );
} );

test( 'disables membership editing when permission is denied', () => {
	// Arrange.
	const product = createProduct();
	const registry = createProductsRegistry( [ product ] );
	vi.mocked( apiFetch ).mockReset();

	// Act.
	const view = render(
		<RegistryProvider value={ registry }>
			<OutletToggle item={ getProductRow( product, false ) } />
		</RegistryProvider>
	);

	// Assert.
	expect( view.getByRole( 'checkbox' ) ).toBeDisabled();
	expect( apiFetch ).not.toHaveBeenCalled();
} );
