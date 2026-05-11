import { useEntityProp } from '@wordpress/core-data';
import { renderHook } from '@testing-library/react';
import useUnsignedIntegerEntityProp from '../';

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

const mockUseEntityProp = useEntityProp as jest.Mock;

describe( 'useUnsignedIntegerEntityProp', () => {
	test( 'returns integer value from entity prop', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [ 2, jest.fn(), undefined ] );

		// Act.
		const { result } = renderHook( () =>
			useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
		);

		// Assert.
		expect( result.current[ 0 ] ).toBe( 2 );
	} );

	test( 'returns zero when entity prop is 0', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [ 0, jest.fn(), undefined ] );

		// Act.
		const { result } = renderHook( () =>
			useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
		);

		// Assert.
		expect( result.current[ 0 ] ).toBe( 0 );
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
			useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
		);

		// Assert.
		expect( result.current[ 0 ] ).toBeUndefined();
	} );

	test( 'returns undefined when entity prop is null', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [ null, jest.fn(), undefined ] );

		// Act.
		const { result } = renderHook( () =>
			useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
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
		renderHook( () =>
			useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
		);

		// Assert.
		expect( mockUseEntityProp ).toHaveBeenCalledWith(
			'root',
			'site',
			'wc_clearance_badge_scale'
		);
	} );

	test( 'exposes setter from entity prop', () => {
		// Arrange.
		const setValue = jest.fn();
		mockUseEntityProp.mockReturnValue( [ 2, setValue, undefined ] );

		// Act.
		const { result } = renderHook( () =>
			useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
		);
		result.current[ 1 ]( 5 );

		// Assert.
		expect( setValue ).toHaveBeenCalledWith( 5 );
	} );

	test( 'setter forwards undefined to clear the value', () => {
		// Arrange.
		const setValue = jest.fn();
		mockUseEntityProp.mockReturnValue( [ 2, setValue, undefined ] );

		// Act.
		const { result } = renderHook( () =>
			useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
		);
		result.current[ 1 ]( undefined );

		// Assert.
		expect( setValue ).toHaveBeenCalledWith( undefined );
	} );

	test( 'setter throws when given a negative integer', () => {
		// Arrange.
		const setValue = jest.fn();
		mockUseEntityProp.mockReturnValue( [ 2, setValue, undefined ] );
		const { result } = renderHook( () =>
			useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
		);

		// Act + Assert.
		expect( () => result.current[ 1 ]( -1 ) ).toThrow(
			'wc_clearance setting "wc_clearance_badge_scale" must be an integer >= 0'
		);
		expect( setValue ).not.toHaveBeenCalled();
	} );

	test( 'setter throws when given a non-integer number', () => {
		// Arrange.
		const setValue = jest.fn();
		mockUseEntityProp.mockReturnValue( [ 2, setValue, undefined ] );
		const { result } = renderHook( () =>
			useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
		);

		// Act + Assert.
		expect( () => result.current[ 1 ]( 1.5 ) ).toThrow(
			'wc_clearance setting "wc_clearance_badge_scale" must be an integer >= 0'
		);
		expect( setValue ).not.toHaveBeenCalled();
	} );

	test( 'setter throws when given NaN', () => {
		// Arrange.
		const setValue = jest.fn();
		mockUseEntityProp.mockReturnValue( [ 2, setValue, undefined ] );
		const { result } = renderHook( () =>
			useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
		);

		// Act + Assert.
		expect( () => result.current[ 1 ]( Number.NaN ) ).toThrow(
			'wc_clearance setting "wc_clearance_badge_scale" must be an integer >= 0'
		);
		expect( setValue ).not.toHaveBeenCalled();
	} );

	test( 'throws when value is a string', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [ 'two', jest.fn(), undefined ] );

		// Expect.
		expect( () =>
			renderHook( () =>
				useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
			)
		).toThrow(
			'wc_clearance setting "wc_clearance_badge_scale" must be an integer >= 0'
		);
		expect( console ).toHaveErrored();
	} );

	test( 'throws when value is a negative integer', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [ -1, jest.fn(), undefined ] );

		// Expect.
		expect( () =>
			renderHook( () =>
				useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
			)
		).toThrow(
			'wc_clearance setting "wc_clearance_badge_scale" must be an integer >= 0'
		);
		expect( console ).toHaveErrored();
	} );

	test( 'throws when value is a non-integer number', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [ 1.5, jest.fn(), undefined ] );

		// Expect.
		expect( () =>
			renderHook( () =>
				useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
			)
		).toThrow(
			'wc_clearance setting "wc_clearance_badge_scale" must be an integer >= 0'
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
			renderHook( () =>
				useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
			)
		).not.toThrow();
	} );

	test( 'does not throw when value is null', () => {
		// Arrange.
		mockUseEntityProp.mockReturnValue( [ null, jest.fn(), undefined ] );

		// Act + Assert: no throw when value is null.
		expect( () =>
			renderHook( () =>
				useUnsignedIntegerEntityProp( 'wc_clearance_badge_scale' )
			)
		).not.toThrow();
	} );
} );
