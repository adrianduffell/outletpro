import { useEntityProp } from '@wordpress/core-data';
import { renderHook } from '@testing-library/react';
import useStringEntityProp from '../';

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

const mockUseEntityProp = useEntityProp as jest.Mock;

describe( 'useStringEntityProp', () => {
	test( 'returns string value from entity prop', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [ 'Sale', jest.fn(), undefined ] );

		// Act.
		const { result } = renderHook( () =>
			useStringEntityProp( 'wc_outlet_badge_label' )
		);

		// Assert.
		expect( result.current[ 0 ] ).toBe( 'Sale' );
	} );

	test( 'returns undefined when entity prop is not set', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [
			undefined,
			jest.fn(),
			undefined,
		] );

		// Act.
		const { result } = renderHook( () =>
			useStringEntityProp( 'wc_outlet_badge_label' )
		);

		// Assert.
		expect( result.current[ 0 ] ).toBeUndefined();
	} );

	test( 'normalizes null entity prop to undefined', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [ null, jest.fn(), undefined ] );

		// Act.
		const { result } = renderHook( () =>
			useStringEntityProp( 'wc_outlet_badge_label' )
		);

		// Assert.
		expect( result.current[ 0 ] ).toBeUndefined();
	} );

	test( 'calls useEntityProp with root, site, and the given key', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [
			undefined,
			jest.fn(),
			undefined,
		] );

		// Act.
		renderHook( () => useStringEntityProp( 'wc_outlet_badge_label' ) );

		// Assert.
		expect( mockUseEntityProp ).toHaveBeenCalledWith(
			'root',
			'site',
			'wc_outlet_badge_label'
		);
	} );

	test( 'exposes setter from entity prop', () => {
		// Arrange.
		const setValue = jest.fn();
		mockUseEntityProp.mockReturnValue( [ 'Sale', setValue, undefined ] );

		// Act.
		const { result } = renderHook( () =>
			useStringEntityProp( 'wc_outlet_badge_label' )
		);
		result.current[ 1 ]( 'Clearance' );

		// Assert.
		expect( setValue ).toHaveBeenCalledWith( 'Clearance' );
	} );

	test( 'setter forwards undefined to clear the value', () => {
		// Arrange.
		const setValue = jest.fn();
		mockUseEntityProp.mockReturnValue( [ 'Sale', setValue, undefined ] );

		// Act.
		const { result } = renderHook( () =>
			useStringEntityProp( 'wc_outlet_badge_label' )
		);
		result.current[ 1 ]( undefined );

		// Assert.
		expect( setValue ).toHaveBeenCalledWith( undefined );
	} );

	test( 'throws when value is not a string', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [ 42, jest.fn(), undefined ] );

		// Expect.
		expect( () =>
			renderHook( () => useStringEntityProp( 'wc_outlet_badge_label' ) )
		).toThrow(
			'wc_outlet setting "wc_outlet_badge_label" must be a string'
		);
		expect( console ).toHaveErrored();
	} );

	test( 'does not throw when value is undefined', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [
			undefined,
			jest.fn(),
			undefined,
		] );

		// Act + Assert: no throw when value is undefined.
		expect( () =>
			renderHook( () => useStringEntityProp( 'wc_outlet_badge_label' ) )
		).not.toThrow();
	} );

	test( 'does not throw when value is null', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [ null, jest.fn(), undefined ] );

		// Act + Assert: no throw when value is null.
		expect( () =>
			renderHook( () => useStringEntityProp( 'wc_outlet_badge_label' ) )
		).not.toThrow();
	} );
} );
