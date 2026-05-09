import useStringEntityProp from '../../use-string-entity-prop';
import { useEntityProp } from '@wordpress/core-data';
import { renderHook } from '@testing-library/react';
import useSettings from '../';

jest.mock( '../../use-string-entity-prop', () => jest.fn() );
jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

const mockUseStringEntityProp = useStringEntityProp as jest.Mock;
const mockUseEntityProp = useEntityProp as jest.Mock;

function setupMock(
	overrides: Record< string, string | undefined > = {},
	scaleOverride?: [ number | undefined, jest.Mock ]
) {
	mockUseStringEntityProp.mockImplementation( ( key: string ) => {
		return [ overrides[ key ], jest.fn() ];
	} );
	mockUseEntityProp.mockReturnValue(
		scaleOverride ?? [ undefined, jest.fn(), undefined ]
	);
}

describe( 'useSettings', () => {
	test( 'returns label value from entity prop', () => {
		// Arrange.
		setupMock( { wc_clearance_badge_label: 'Sale' } );

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
		setupMock( { wc_clearance_badge_bg_color: '#FFEE85' } );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.bgColor ).toBe( '#FFEE85' );
	} );

	test( 'returns text color value from entity prop', () => {
		// Arrange.
		setupMock( { wc_clearance_badge_text_color: '#333333' } );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.textColor ).toBe( '#333333' );
	} );

	test( 'returns font size value from entity prop', () => {
		// Arrange.
		setupMock( { wc_clearance_badge_font_size: '0.875rem' } );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.fontSize ).toBe( '0.875rem' );
	} );

	test( 'returns font weight value from entity prop', () => {
		// Arrange.
		setupMock( { wc_clearance_badge_font_weight: '700' } );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.fontWeight ).toBe( '700' );
	} );

	test( 'returns border radius value from entity prop', () => {
		// Arrange.
		setupMock( { wc_clearance_badge_border_radius: '4px' } );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.borderRadius ).toBe( '4px' );
	} );

	test( 'returns padding values from entity props', () => {
		// Arrange.
		setupMock( {
			wc_clearance_badge_padding_top: '8px',
			wc_clearance_badge_padding_right: '12px',
			wc_clearance_badge_padding_bottom: '8px',
			wc_clearance_badge_padding_left: '12px',
		} );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.paddingTop ).toBe( '8px' );
		expect( result.current.paddingRight ).toBe( '12px' );
		expect( result.current.paddingBottom ).toBe( '8px' );
		expect( result.current.paddingLeft ).toBe( '12px' );
	} );

	test( 'returns scale value from entity prop', () => {
		// Arrange.
		setupMock( {}, [ 140, jest.fn() ] );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.scale ).toBe( 140 );
	} );

	test( 'exposes setLabel setter from entity prop', () => {
		// Arrange.
		const setLabel = jest.fn();
		mockUseStringEntityProp.mockImplementation( ( key: string ) => {
			if ( key === 'wc_clearance_badge_label' ) {
				return [ 'Clearance', setLabel ];
			}
			return [ undefined, jest.fn() ];
		} );

		// Act.
		const { result } = renderHook( () => useSettings() );
		result.current.setLabel( 'Sale' );

		// Assert.
		expect( setLabel ).toHaveBeenCalledWith( 'Sale' );
	} );

	test( 'exposes all 15 setters wired to the correct entity prop key', () => {
		// Arrange.
		const setters: Record< string, jest.Mock > = {};
		const keyToSetter: Record< string, string > = {
			wc_clearance_badge_label: 'setLabel',
			wc_clearance_badge_text_color: 'setTextColor',
			wc_clearance_badge_bg_color: 'setBgColor',
			wc_clearance_badge_font_size: 'setFontSize',
			wc_clearance_badge_font_weight: 'setFontWeight',
			wc_clearance_badge_border_color: 'setBorderColor',
			wc_clearance_badge_border_style: 'setBorderStyle',
			wc_clearance_badge_border_width: 'setBorderWidth',
			wc_clearance_badge_border_radius: 'setBorderRadius',
			wc_clearance_badge_padding_top: 'setPaddingTop',
			wc_clearance_badge_padding_right: 'setPaddingRight',
			wc_clearance_badge_padding_bottom: 'setPaddingBottom',
			wc_clearance_badge_padding_left: 'setPaddingLeft',
			wc_clearance_message: 'setMessage',
		};
		for ( const key of Object.keys( keyToSetter ) ) {
			setters[ key ] = jest.fn();
		}
		mockUseStringEntityProp.mockImplementation( ( key: string ) => [
			undefined,
			setters[ key ] ?? jest.fn(),
		] );
		const scaleSetter = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			undefined,
			scaleSetter,
			undefined,
		] );

		// Act.
		const { result } = renderHook( () => useSettings() );
		result.current.setLabel( 'a' );
		result.current.setTextColor( 'b' );
		result.current.setBgColor( 'c' );
		result.current.setFontSize( 'd' );
		result.current.setFontWeight( 'e' );
		result.current.setBorderColor( 'f' );
		result.current.setBorderStyle( 'g' );
		result.current.setBorderWidth( 'h' );
		result.current.setBorderRadius( 'i' );
		result.current.setPaddingTop( 'j' );
		result.current.setPaddingRight( 'k' );
		result.current.setPaddingBottom( 'l' );
		result.current.setPaddingLeft( 'm' );
		result.current.setScale( 140 );
		result.current.setMessage( 'n' );

		// Assert.
		expect( setters.wc_clearance_badge_label ).toHaveBeenCalledWith( 'a' );
		expect( setters.wc_clearance_badge_text_color ).toHaveBeenCalledWith(
			'b'
		);
		expect( setters.wc_clearance_badge_bg_color ).toHaveBeenCalledWith(
			'c'
		);
		expect( setters.wc_clearance_badge_font_size ).toHaveBeenCalledWith(
			'd'
		);
		expect( setters.wc_clearance_badge_font_weight ).toHaveBeenCalledWith(
			'e'
		);
		expect( setters.wc_clearance_badge_border_color ).toHaveBeenCalledWith(
			'f'
		);
		expect( setters.wc_clearance_badge_border_style ).toHaveBeenCalledWith(
			'g'
		);
		expect( setters.wc_clearance_badge_border_width ).toHaveBeenCalledWith(
			'h'
		);
		expect( setters.wc_clearance_badge_border_radius ).toHaveBeenCalledWith(
			'i'
		);
		expect( setters.wc_clearance_badge_padding_top ).toHaveBeenCalledWith(
			'j'
		);
		expect( setters.wc_clearance_badge_padding_right ).toHaveBeenCalledWith(
			'k'
		);
		expect(
			setters.wc_clearance_badge_padding_bottom
		).toHaveBeenCalledWith( 'l' );
		expect( setters.wc_clearance_badge_padding_left ).toHaveBeenCalledWith(
			'm'
		);
		expect( scaleSetter ).toHaveBeenCalledWith( 140 );
		expect( setters.wc_clearance_message ).toHaveBeenCalledWith( 'n' );
	} );

	test( 'calls useStringEntityProp for all 14 string settings and useEntityProp for scale', () => {
		// Arrange.
		mockUseStringEntityProp.mockClear();
		mockUseEntityProp.mockClear();
		setupMock();

		// Act.
		renderHook( () => useSettings() );

		// Assert.
		const keys = mockUseStringEntityProp.mock.calls.map(
			( call: [ string ] ) => call[ 0 ]
		);
		expect( keys ).toContain( 'wc_clearance_badge_label' );
		expect( keys ).toContain( 'wc_clearance_badge_text_color' );
		expect( keys ).toContain( 'wc_clearance_badge_bg_color' );
		expect( keys ).toContain( 'wc_clearance_badge_font_size' );
		expect( keys ).toContain( 'wc_clearance_badge_font_weight' );
		expect( keys ).toContain( 'wc_clearance_badge_border_color' );
		expect( keys ).toContain( 'wc_clearance_badge_border_style' );
		expect( keys ).toContain( 'wc_clearance_badge_border_width' );
		expect( keys ).toContain( 'wc_clearance_badge_border_radius' );
		expect( keys ).toContain( 'wc_clearance_badge_padding_top' );
		expect( keys ).toContain( 'wc_clearance_badge_padding_right' );
		expect( keys ).toContain( 'wc_clearance_badge_padding_bottom' );
		expect( keys ).toContain( 'wc_clearance_badge_padding_left' );
		expect( keys ).toContain( 'wc_clearance_message' );
		expect( keys ).toHaveLength( 14 );
		expect( mockUseEntityProp ).toHaveBeenCalledWith(
			'root',
			'site',
			'wc_clearance_badge_scale'
		);
		expect( mockUseEntityProp ).toHaveBeenCalledTimes( 1 );
	} );
} );
