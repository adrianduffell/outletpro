import useStringEntityProp from '../../use-string-entity-prop';
import { renderHook } from '@testing-library/react';
import useSettings from '../';

jest.mock( '../../use-string-entity-prop', () => jest.fn() );

const mockUseStringEntityProp = useStringEntityProp as jest.Mock;

function setupMock( overrides: Record< string, string | undefined > = {} ) {
	mockUseStringEntityProp.mockImplementation( ( key: string ) => {
		return [ overrides[ key ], jest.fn() ];
	} );
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

	test( 'calls useStringEntityProp for all 13 badge settings', () => {
		// Arrange.
		mockUseStringEntityProp.mockClear();
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
		expect( keys ).toHaveLength( 13 );
	} );
} );
