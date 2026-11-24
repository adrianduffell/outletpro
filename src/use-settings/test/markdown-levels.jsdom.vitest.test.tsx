/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { act, renderHook } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { store as coreStore } from '@wordpress/core-data';
import { expect, test, vi } from 'vitest';
import useSettings from '..';
import { assertArrayUnsignedIntegersValue } from '../use-array-unsigned-integers-entity-prop';
import { createSettingsRegistry } from '../test-utils';

vi.mock( '@wordpress/api-fetch', () => ( { default: vi.fn() } ) );

test.each( [
	{ levels: [] },
	{ levels: [ 70, 0, 101 ] },
	{ levels: [ 30, 30 ] },
] )( 'reads valid markdown levels $levels', ( { levels } ) => {
	// Arrange.
	const { wrapper } = createSettingsRegistry( {
		outletpro_markdown_levels: levels,
	} );
	// Act.
	const { result } = renderHook( useSettings, { wrapper } );
	// Assert.
	expect( result.current.markdownLevels ).toEqual( levels );
} );

test.each( [ { value: undefined }, { value: null } ] )(
	'normalizes absent markdown levels $value',
	( { value } ) => {
		// Arrange.
		const { wrapper } = createSettingsRegistry( {
			outletpro_markdown_levels: value,
		} );
		// Act.
		const { result } = renderHook( useSettings, { wrapper } );
		// Assert.
		expect( result.current.markdownLevels ).toBeUndefined();
	}
);

test( 'shares local edits through core-data and reverts them without saving', () => {
	// Arrange.
	vi.mocked( apiFetch ).mockReset();
	const original = [ 30, 50, 70 ];
	const { registry, wrapper } = createSettingsRegistry( {
		outletpro_markdown_levels: original,
		outletpro_message: 'Original message',
	} );
	const first = renderHook( useSettings, { wrapper } );
	const second = renderHook( useSettings, { wrapper } );
	// Act.
	act( () => {
		first.result.current.setMessage( 'Pending message' );
		first.result.current.setMarkdownLevels( [] );
	} );
	// Assert.
	expect( second.result.current.markdownLevels ).toEqual( [] );
	expect(
		registry.select( coreStore ).getEntityRecord( 'root', 'site' )
	).toMatchObject( { outletpro_markdown_levels: original } );
	expect(
		registry.select( coreStore ).getEntityRecordEdits( 'root', 'site' )
	).toEqual( {
		outletpro_markdown_levels: [],
		outletpro_message: 'Pending message',
	} );
	act( () => first.result.current.setMarkdownLevels( original ) );
	expect(
		registry.select( coreStore ).getEntityRecordEdits( 'root', 'site' )
	).toEqual( { outletpro_message: 'Pending message' } );
	expect( apiFetch ).not.toHaveBeenCalled();
} );

test.each( [
	{ value: 40 },
	{ value: {} },
	{ value: [ '40' ] },
	{ value: [ 1.5 ] },
	{ value: [ -1 ] },
	{ value: [ NaN ] },
	{ value: [ Infinity ] },
] )( 'rejects invalid unsigned integer arrays $value', ( { value } ) => {
	// Arrange.
	// Expect and act.
	expect( () => assertArrayUnsignedIntegersValue( value ) ).toThrow();
} );

test.each( [
	{ levels: [ -1 ] },
	{ levels: [ 1.5 ] },
	{ levels: [ NaN ] },
	{ levels: [ Infinity ] },
] )( 'rejects invalid markdown edits $levels', ( { levels } ) => {
	// Arrange.
	const { registry, wrapper } = createSettingsRegistry( {
		outletpro_markdown_levels: [ 30 ],
	} );
	const { result } = renderHook( useSettings, { wrapper } );
	// Expect and act.
	expect( () => result.current.setMarkdownLevels( levels ) ).toThrow();
	expect(
		registry.select( coreStore ).getEntityRecordEdits( 'root', 'site' )
	).toBeUndefined();
} );

test( 'edits price levels without imposing range or uniqueness rules', () => {
	// Arrange.
	const { registry, wrapper } = createSettingsRegistry( {
		outletpro_price_levels: [ 10, 25 ],
	} );
	const { result } = renderHook( useSettings, { wrapper } );
	// Act.
	act( () => result.current.setPriceLevels( [ 0, 250, 250 ] ) );
	// Assert.
	expect( result.current.priceLevels ).toEqual( [ 0, 250, 250 ] );
	expect(
		registry.select( coreStore ).getEntityRecordEdits( 'root', 'site' )
	).toEqual( { outletpro_price_levels: [ 0, 250, 250 ] } );
} );
