/**
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

import useStringEntityProp from '../../use-string-entity-prop';
import useUnsignedIntegerEntityProp from '../../use-unsigned-integer-entity-prop';
import { renderHook } from '@testing-library/react';
import useSettings from '../';

jest.mock( '../../use-string-entity-prop', () => jest.fn() );
jest.mock( '../../use-unsigned-integer-entity-prop', () => jest.fn() );

const mockUseStringEntityProp = useStringEntityProp as jest.Mock;
const mockUseUnsignedIntegerEntityProp =
	useUnsignedIntegerEntityProp as jest.Mock;

function setupMock(
	overrides: Record< string, string | undefined > = {},
	unsignedIntegerOverrides: Array< [ number | undefined, jest.Mock ] > = [
		[ undefined, jest.fn() ],
		[ undefined, jest.fn() ],
	]
) {
	mockUseStringEntityProp.mockImplementation( ( key: string ) => {
		return [ overrides[ key ], jest.fn() ];
	} );
	mockUseUnsignedIntegerEntityProp.mockReset();
	unsignedIntegerOverrides.forEach( ( value ) => {
		mockUseUnsignedIntegerEntityProp.mockReturnValueOnce( value );
	} );
}

describe( 'useSettings', () => {
	test( 'returns label value from entity prop', () => {
		// Arrange.
		setupMock( { outletpro_badge_label: 'Sale' } );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.label ).toBe( 'Sale' );
	} );

	test( 'returns undefined label when entity prop is not set', () => {
		// Arrange.
		setupMock();

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.label ).toBeUndefined();
	} );

	test( 'returns bg color value from entity prop', () => {
		// Arrange.
		setupMock( { outletpro_badge_bg_color: '#FFEE85' } );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.bgColor ).toBe( '#FFEE85' );
	} );

	test( 'returns text color value from entity prop', () => {
		// Arrange.
		setupMock( { outletpro_badge_text_color: '#333333' } );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.textColor ).toBe( '#333333' );
	} );

	test( 'returns font weight value from entity prop', () => {
		// Arrange.
		setupMock( { outletpro_badge_font_weight: '700' } );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.fontWeight ).toBe( '700' );
	} );

	test( 'returns border radius value from entity prop', () => {
		// Arrange.
		setupMock( { outletpro_badge_border_radius: '4px' } );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.borderRadius ).toBe( '4px' );
	} );

	test( 'returns scale value from entity prop', () => {
		// Arrange.
		setupMock( {}, [
			[ 140, jest.fn() ],
			[ undefined, jest.fn() ],
		] );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.scale ).toBe( 140 );
	} );

	test( 'returns density value from entity prop', () => {
		// Arrange.
		setupMock( {}, [
			[ undefined, jest.fn() ],
			[ 80, jest.fn() ],
		] );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.density ).toBe( 80 );
	} );

	test( 'exposes setLabel setter from entity prop', () => {
		// Arrange.
		const setLabel = jest.fn();
		mockUseStringEntityProp.mockImplementation( ( key: string ) => {
			if ( key === 'outletpro_badge_label' ) {
				return [ 'Clearance', setLabel ];
			}
			return [ undefined, jest.fn() ];
		} );
		mockUseUnsignedIntegerEntityProp
			.mockReturnValueOnce( [ undefined, jest.fn() ] )
			.mockReturnValueOnce( [ undefined, jest.fn() ] );

		// Act.
		const { result } = renderHook( () => useSettings() );
		result.current.setLabel( 'Sale' );

		// Assert.
		expect( setLabel ).toHaveBeenCalledWith( 'Sale' );
	} );

	test( 'exposes setters wired to the correct entity prop key', () => {
		// Arrange.
		const setters: Record< string, jest.Mock > = {};
		const keyToSetter: Record< string, string > = {
			outletpro_badge_label: 'setLabel',
			outletpro_badge_text_color: 'setTextColor',
			outletpro_badge_bg_color: 'setBgColor',
			outletpro_badge_font_weight: 'setFontWeight',
			outletpro_badge_border_color: 'setBorderColor',
			outletpro_badge_border_style: 'setBorderStyle',
			outletpro_badge_border_width: 'setBorderWidth',
			outletpro_badge_border_radius: 'setBorderRadius',
			outletpro_message: 'setMessage',
		};
		for ( const key of Object.keys( keyToSetter ) ) {
			setters[ key ] = jest.fn();
		}
		mockUseStringEntityProp.mockImplementation( ( key: string ) => [
			undefined,
			setters[ key ] ?? jest.fn(),
		] );
		const scaleSetter = jest.fn();
		const densitySetter = jest.fn();
		mockUseUnsignedIntegerEntityProp
			.mockReturnValueOnce( [ undefined, scaleSetter ] )
			.mockReturnValueOnce( [ undefined, densitySetter ] );

		// Act.
		const { result } = renderHook( () => useSettings() );
		result.current.setLabel( 'a' );
		result.current.setTextColor( 'b' );
		result.current.setBgColor( 'c' );
		result.current.setFontWeight( 'e' );
		result.current.setBorderColor( 'f' );
		result.current.setBorderStyle( 'g' );
		result.current.setBorderWidth( 'h' );
		result.current.setBorderRadius( 'i' );
		result.current.setScale( 140 );
		result.current.setDensity( 80 );
		result.current.setMessage( 'o' );

		// Assert.
		expect( setters.outletpro_badge_label ).toHaveBeenCalledWith( 'a' );
		expect( setters.outletpro_badge_text_color ).toHaveBeenCalledWith(
			'b'
		);
		expect( setters.outletpro_badge_bg_color ).toHaveBeenCalledWith( 'c' );
		expect( setters.outletpro_badge_font_weight ).toHaveBeenCalledWith(
			'e'
		);
		expect( setters.outletpro_badge_border_color ).toHaveBeenCalledWith(
			'f'
		);
		expect( setters.outletpro_badge_border_style ).toHaveBeenCalledWith(
			'g'
		);
		expect( setters.outletpro_badge_border_width ).toHaveBeenCalledWith(
			'h'
		);
		expect( setters.outletpro_badge_border_radius ).toHaveBeenCalledWith(
			'i'
		);
		expect( scaleSetter ).toHaveBeenCalledWith( 140 );
		expect( densitySetter ).toHaveBeenCalledWith( 80 );
		expect( setters.outletpro_message ).toHaveBeenCalledWith( 'o' );
	} );

	test( 'calls useStringEntityProp for string settings and useUnsignedIntegerEntityProp for scale and density', () => {
		// Arrange.
		mockUseStringEntityProp.mockClear();
		mockUseUnsignedIntegerEntityProp.mockClear();
		setupMock();

		// Act.
		renderHook( () => useSettings() );

		// Assert.
		const keys = mockUseStringEntityProp.mock.calls.map(
			( call: [ string ] ) => call[ 0 ]
		);
		expect( keys ).toContain( 'outletpro_badge_label' );
		expect( keys ).toContain( 'outletpro_badge_text_color' );
		expect( keys ).toContain( 'outletpro_badge_bg_color' );
		expect( keys ).toContain( 'outletpro_badge_font_weight' );
		expect( keys ).toContain( 'outletpro_badge_border_color' );
		expect( keys ).toContain( 'outletpro_badge_border_style' );
		expect( keys ).toContain( 'outletpro_badge_border_width' );
		expect( keys ).toContain( 'outletpro_badge_border_radius' );
		expect( keys ).toContain( 'outletpro_message' );
		expect( mockUseUnsignedIntegerEntityProp ).toHaveBeenNthCalledWith(
			1,
			'outletpro_badge_scale'
		);
		expect( mockUseUnsignedIntegerEntityProp ).toHaveBeenNthCalledWith(
			2,
			'outletpro_badge_density'
		);
		expect( mockUseUnsignedIntegerEntityProp ).toHaveBeenCalledTimes( 2 );
	} );
} );
