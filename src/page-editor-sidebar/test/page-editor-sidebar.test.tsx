import type { ReactNode } from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { registerPlugin } from '@wordpress/plugins';
import { useEntityProp } from '@wordpress/core-data';

jest.mock( '@wordpress/plugins', () => ( {
	registerPlugin: jest.fn(),
} ) );

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

jest.mock( '@wordpress/element', () => ( {
	...jest.requireActual( '@wordpress/element' ),
	useMemo: jest.fn( ( fn: () => unknown ) => fn() ),
} ) );

jest.mock( '@wordpress/components', () => ( {
	BaseControl: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
	),
	CustomSelectControl: ( {
		label,
		options,
		value,
		onChange,
	}: {
		label: string;
		options: Array< { key: string; name: string } >;
		value: { key: string; name: string };
		onChange: ( {
			selectedItem,
		}: {
			selectedItem: { key: string };
		} ) => void;
	} ) => (
		<select
			aria-label={ label }
			value={ value?.key }
			onChange={ ( e ) => {
				const selected = options.find(
					( o ) => o.key === e.target.value
				);
				if ( selected ) {
					onChange( { selectedItem: selected } );
				}
			} }
		>
			{ options.map( ( o ) => (
				<option key={ o.key } value={ o.key }>
					{ o.name }
				</option>
			) ) }
		</select>
	),
	FontSizePicker: ( {
		value,
		onChange,
	}: {
		value: string | undefined;
		onChange: ( v: string | undefined ) => void;
	} ) => (
		<input
			aria-label="Font size"
			value={ value ?? '' }
			onChange={ ( e ) => onChange( e.target.value || undefined ) }
		/>
	),
	PanelBody: ( {
		children,
		title,
	}: {
		children: ReactNode;
		title?: string;
	} ) => (
		<div>
			{ title && <h2>{ title }</h2> }
			{ children }
		</div>
	),
	TextControl: ( {
		label,
		value,
		onChange,
	}: {
		label: string;
		value: string;
		onChange: ( v: string ) => void;
	} ) => (
		<input
			aria-label={ label }
			value={ value }
			onChange={ ( e ) => onChange( e.target.value ) }
		/>
	),
	__experimentalBorderControl: ( {
		value,
		onChange,
	}: {
		value: { color?: string; style?: string; width?: string };
		onChange: (
			v: { color?: string; style?: string; width?: string } | undefined
		) => void;
	} ) => (
		<input
			aria-label="Border"
			value={ value?.width ?? '' }
			onChange={ ( e ) =>
				onChange( { ...value, width: e.target.value } )
			}
		/>
	),
	__experimentalBoxControl: ( {
		label,
		values,
		onChange,
	}: {
		label: string;
		values: {
			top?: string;
			right?: string;
			bottom?: string;
			left?: string;
		};
		onChange: ( v: {
			top?: string;
			right?: string;
			bottom?: string;
			left?: string;
		} ) => void;
	} ) => (
		<input
			aria-label={ label }
			value={ values?.top ?? '' }
			onChange={ ( e ) => onChange( { ...values, top: e.target.value } ) }
		/>
	),
	__experimentalUnitControl: ( {
		value,
		onChange,
	}: {
		value: string | undefined;
		onChange: ( v: string | undefined ) => void;
	} ) => (
		<input
			aria-label="Border radius"
			value={ value ?? '' }
			onChange={ ( e ) => onChange( e.target.value || undefined ) }
		/>
	),
} ) );

jest.mock( '@wordpress/block-editor', () => ( {
	PanelColorSettings: ( {
		title,
		colorSettings,
	}: {
		title: string;
		colorSettings: Array< {
			label: string;
			value: string;
			onChange: ( v: string | undefined ) => void;
		} >;
	} ) => (
		<div>
			<h2>{ title }</h2>
			{ colorSettings.map( ( setting ) => (
				<input
					key={ setting.label }
					aria-label={ setting.label }
					value={ setting.value }
					onChange={ ( e ) =>
						setting.onChange( e.target.value || undefined )
					}
				/>
			) ) }
		</div>
	),
} ) );

jest.mock( '@wordpress/editor', () => ( {
	PluginSidebar: ( {
		children,
		title,
	}: {
		children: ReactNode;
		title: string;
	} ) => (
		<section>
			<h1>{ title }</h1>
			{ children }
		</section>
	),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( text: string ) => text ),
} ) );

const mockRegisterPlugin = registerPlugin as jest.Mock;
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

describe( 'page-editor-sidebar registration', () => {
	test( 'registers the sidebar plugin with expected name and render function', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		setupEntityPropMock();

		// Act.
		jest.isolateModules( () => {
			require( '../index' );
		} );

		// Assert.
		expect( mockRegisterPlugin ).toHaveBeenCalledWith(
			'wc-clearance-sidebar',
			expect.objectContaining( {
				render: expect.any( Function ),
			} )
		);
	} );

	test( 'render function outputs the sidebar title and description', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		setupEntityPropMock();
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect( screen.getByText( 'Clearance section' ) ).toBeInTheDocument();
		expect(
			screen.getByText(
				'Customize the appearance of the clearance section. Changes apply to the whole site.'
			)
		).toBeInTheDocument();
	} );

	test( 'render function outputs all panel sections', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		setupEntityPropMock();
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect( screen.getByText( 'Badge' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Typography' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Badge color' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Dimensions' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Border' ) ).toBeInTheDocument();
	} );

	test( 'badge label control shows stored value', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setLabel = jest.fn();
		setupEntityPropMock( {
			wc_clearance_badge_label: [ 'Sale', setLabel ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect(
			screen.getByRole( 'textbox', { name: 'Badge label' } )
		).toHaveValue( 'Sale' );
	} );

	test( 'badge label control falls back to default when setting is empty', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		setupEntityPropMock( {
			wc_clearance_badge_label: [ undefined, jest.fn() ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect(
			screen.getByRole( 'textbox', { name: 'Badge label' } )
		).toHaveValue( 'Clearance' );
	} );

	test( 'badge label control calls setter when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setLabel = jest.fn();
		setupEntityPropMock( {
			wc_clearance_badge_label: [ 'Clearance', setLabel ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const input = screen.getByRole( 'textbox', { name: 'Badge label' } );

		// Act.
		fireEvent.change( input, { target: { value: 'Discounts' } } );

		// Assert.
		expect( setLabel ).toHaveBeenCalledWith( 'Discounts' );
	} );

	test( 'font size control shows stored value', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		setupEntityPropMock( {
			wc_clearance_badge_font_size: [ '1rem', jest.fn() ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect(
			screen.getByRole( 'textbox', { name: 'Font size' } )
		).toHaveValue( '1rem' );
	} );

	test( 'font weight control calls setter when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setFontWeight = jest.fn();
		setupEntityPropMock( {
			wc_clearance_badge_font_weight: [ '', setFontWeight ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const select = screen.getByRole( 'combobox', { name: 'Font weight' } );

		// Act.
		fireEvent.change( select, { target: { value: '700' } } );

		// Assert.
		expect( setFontWeight ).toHaveBeenCalledWith( '700' );
	} );

	test( 'text color control shows stored value', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		setupEntityPropMock( {
			wc_clearance_badge_text_color: [ '#ff0000', jest.fn() ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect( screen.getByRole( 'textbox', { name: 'Text' } ) ).toHaveValue(
			'#ff0000'
		);
	} );

	test( 'background color control falls back to default when setting is empty', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		setupEntityPropMock( {
			wc_clearance_badge_bg_color: [ undefined, jest.fn() ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect(
			screen.getByRole( 'textbox', { name: 'Background' } )
		).toHaveValue( '#FFEE85' );
	} );

	test( 'border radius control calls setter when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setBorderRadius = jest.fn();
		setupEntityPropMock( {
			wc_clearance_badge_border_radius: [ '2px', setBorderRadius ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const input = screen.getByRole( 'textbox', {
			name: 'Border radius',
		} );

		// Act.
		fireEvent.change( input, { target: { value: '8px' } } );

		// Assert.
		expect( setBorderRadius ).toHaveBeenCalledWith( '8px' );
	} );

	test( 'padding control calls setters for each side when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setPaddingTop = jest.fn();
		const setPaddingRight = jest.fn();
		const setPaddingBottom = jest.fn();
		const setPaddingLeft = jest.fn();
		setupEntityPropMock( {
			wc_clearance_badge_padding_top: [ '5px', setPaddingTop ],
			wc_clearance_badge_padding_right: [ '5px', setPaddingRight ],
			wc_clearance_badge_padding_bottom: [ '5px', setPaddingBottom ],
			wc_clearance_badge_padding_left: [ '5px', setPaddingLeft ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const input = screen.getByRole( 'textbox', { name: 'Padding' } );

		// Act.
		fireEvent.change( input, { target: { value: '10px' } } );

		// Assert.
		expect( setPaddingTop ).toHaveBeenCalledWith( '10px' );
		expect( setPaddingRight ).toHaveBeenCalledWith( '5px' );
		expect( setPaddingBottom ).toHaveBeenCalledWith( '5px' );
		expect( setPaddingLeft ).toHaveBeenCalledWith( '5px' );
	} );
} );
