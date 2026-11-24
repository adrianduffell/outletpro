/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import apiFetch from '@wordpress/api-fetch';
import { store as coreStore } from '@wordpress/core-data';
import { createRegistry, select } from '@wordpress/data';
import { expect, test, vi } from 'vitest';
import { registerProductEntity } from '..';
import { PRODUCT_ENTITY } from '../product-entity';
import { createProduct } from '../test-utils/store-fixtures';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

test( 'registers the product entity in the default registry without fetching', () => {
	// Arrange.
	vi.mocked( apiFetch ).mockReset();
	const { kind, name } = PRODUCT_ENTITY;

	// Act.
	registerProductEntity();

	// Assert.
	expect( select( coreStore ).getEntityConfig( kind, name ) ).toEqual(
		PRODUCT_ENTITY
	);
	expect( apiFetch ).not.toHaveBeenCalled();
} );

test( 'supports an isolated registry and preserves cached records when registered again', () => {
	// Arrange.
	const registry = createRegistry();
	registry.register( coreStore );
	const { kind, name } = PRODUCT_ENTITY;
	const product = createProduct();
	vi.mocked( apiFetch ).mockReset();
	registerProductEntity( registry );
	registry
		.dispatch( coreStore )
		.receiveEntityRecords( kind, name, [ product ] );
	registry
		.dispatch( coreStore )
		.finishResolution( 'getEntityRecord', [ kind, name, product.id ] );

	// Act.
	registerProductEntity( registry );

	// Assert.
	expect(
		registry.select( coreStore ).getEntityConfig( kind, name )
	).toEqual( PRODUCT_ENTITY );
	expect( registry.select( coreStore ).getEntitiesConfig( kind ) ).toEqual( [
		PRODUCT_ENTITY,
	] );
	expect(
		registry.select( coreStore ).getEntityRecord( kind, name, product.id )
	).toEqual( product );
	expect( apiFetch ).not.toHaveBeenCalled();
} );
