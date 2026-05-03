import { useEntityProp } from '@wordpress/core-data';
import { renderHook } from '@testing-library/react';
import useSettings from '../';

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

const mockUseEntityProp = useEntityProp as jest.Mock;

function setupEntityPropMock(
	overrides: Record< string, [ string | undefined, jest.Mock ] > = {}
) {
	mockUseEntityProp.mockImplementation(
		( _kind: string, _name: string, key: string ) => {
			if ( overrides[ key ] ) {
				return [ ...overrides[ key ], undefined ];
			}
			return [ undefined, jest.fn(), undefined ];
		}
	);
}

describe( 'useSettings', () => {
	test( 'returns label value from entity prop', () => {
		// Arrange.
		setupEntityPropMock( {
			wc_clearance_badge_label: [ 'Sale', jest.fn() ],
		} );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.label ).toBe( 'Sale' );
	} );

	test( 'returns undefined label when entity prop is not set', () => {
		// Arrange.
		setupEntityPropMock();

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.label ).toBeUndefined();
	} );

	test( 'returns bg color value from entity prop', () => {
		// Arrange.
		setupEntityPropMock( {
			wc_clearance_badge_bg_color: [ '#FFEE85', jest.fn() ],
		} );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.bgColor ).toBe( '#FFEE85' );
	} );

	test( 'returns text color value from entity prop', () => {
		// Arrange.
		setupEntityPropMock( {
			wc_clearance_badge_text_color: [ '#333333', jest.fn() ],
		} );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.textColor ).toBe( '#333333' );
	} );

	test( 'returns font size value from entity prop', () => {
		// Arrange.
		setupEntityPropMock( {
			wc_clearance_badge_font_size: [ '0.875rem', jest.fn() ],
		} );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.fontSize ).toBe( '0.875rem' );
	} );

	test( 'returns font weight value from entity prop', () => {
		// Arrange.
		setupEntityPropMock( {
			wc_clearance_badge_font_weight: [ '700', jest.fn() ],
		} );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.fontWeight ).toBe( '700' );
	} );

	test( 'returns border radius value from entity prop', () => {
		// Arrange.
		setupEntityPropMock( {
			wc_clearance_badge_border_radius: [ '4px', jest.fn() ],
		} );

		// Act.
		const { result } = renderHook( () => useSettings() );

		// Assert.
		expect( result.current.borderRadius ).toBe( '4px' );
	} );

	test( 'returns padding values from entity props', () => {
		// Arrange.
		setupEntityPropMock( {
			wc_clearance_badge_padding_top: [ '8px', jest.fn() ],
			wc_clearance_badge_padding_right: [ '12px', jest.fn() ],
			wc_clearance_badge_padding_bottom: [ '8px', jest.fn() ],
			wc_clearance_badge_padding_left: [ '12px', jest.fn() ],
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
		setupEntityPropMock( {
			wc_clearance_badge_label: [ 'Clearance', setLabel ],
		} );

		// Act.
		const { result } = renderHook( () => useSettings() );
		result.current.setLabel( 'Sale' );

		// Assert.
		expect( setLabel ).toHaveBeenCalledWith( 'Sale' );
	} );

	test( 'calls useEntityProp for all 13 badge settings', () => {
		// Arrange.
		mockUseEntityProp.mockClear();
		setupEntityPropMock();

		// Act.
		renderHook( () => useSettings() );

		// Assert.
		const keys = mockUseEntityProp.mock.calls.map(
			( call: [ string, string, string ] ) => call[ 2 ]
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
