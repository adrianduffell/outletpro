import type { ReactNode } from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { registerPlugin } from '@wordpress/plugins';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

jest.mock( '@wordpress/plugins', () => ( {
	registerPlugin: jest.fn(),
} ) );

jest.mock( '@wordpress/data', () => ( {
	useSelect: jest.fn(),
} ) );

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
	store: {},
} ) );

jest.mock( '@wordpress/element', () => ( {
	...jest.requireActual( '@wordpress/element' ),
	useMemo: jest.fn( ( fn: () => unknown ) => fn() ),
	useEffect: jest.fn(),
} ) );

jest.mock( '@wordpress/components', () => ( {
	BaseControl: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
	),
	TabPanel: ( {
		children,
		tabs,
	}: {
		children: ( tab: { name: string; title: string } ) => ReactNode;
		tabs: { name: string; title: string }[];
	} ) => (
		<section>
			<div role="tablist">
				{ tabs.map( ( tab ) => (
					<button key={ tab.name } role="tab">
						{ tab.title }
					</button>
				) ) }
			</div>
			{ children( tabs[ 0 ] ) }
		</section>
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
	BorderControl: ( {
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
	BoxControl: ( {
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
			aria-label="Radius"
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
const mockUseSelect = useSelect as jest.Mock;

function setupEntityPropMock(
	overrides: Record< string, [ string | undefined, jest.Mock ] > = {}
) {
	mockUseSelect.mockReturnValue( true );
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

	test( 'render function renders nothing when site record is not available', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		mockUseSelect.mockReturnValue( false );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		const { container } = render( pluginConfig.render() );

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );

	test( 'render function outputs the sidebar title', () => {
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
		expect( screen.getByText( 'Label' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Typography' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Color' ) ).toBeInTheDocument();
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
		expect( screen.getByRole( 'textbox', { name: 'Label' } ) ).toHaveValue(
			'Sale'
		);
	} );

	test( 'badge label control is empty when setting is empty', () => {
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
		expect( screen.getByRole( 'textbox', { name: 'Label' } ) ).toHaveValue(
			''
		);
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
		const input = screen.getByRole( 'textbox', { name: 'Label' } );

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

	test( 'background color control is empty when setting is empty', () => {
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
		).toHaveValue( '' );
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
			name: 'Radius',
		} );

		// Act.
		fireEvent.change( input, { target: { value: '8px' } } );

		// Assert.
		expect( setBorderRadius ).toHaveBeenCalledWith( '8px' );
	} );

	test( 'padding control only calls setter for sides that changed', () => {
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

		// Assert: only top changed, the rest are unchanged and must not be called.
		expect( setPaddingTop ).toHaveBeenCalledWith( '10px' );
		expect( setPaddingRight ).not.toHaveBeenCalled();
		expect( setPaddingBottom ).not.toHaveBeenCalled();
		expect( setPaddingLeft ).not.toHaveBeenCalled();
	} );

	test( 'font size control calls setter when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setFontSize = jest.fn();
		setupEntityPropMock( {
			wc_clearance_badge_font_size: [ '0.875rem', setFontSize ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const input = screen.getByRole( 'textbox', { name: 'Font size' } );

		// Act.
		fireEvent.change( input, { target: { value: '1rem' } } );

		// Assert.
		expect( setFontSize ).toHaveBeenCalledWith( '1rem' );
	} );

	test( 'text color control calls setter when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setTextColor = jest.fn();
		setupEntityPropMock( {
			wc_clearance_badge_text_color: [ '#222', setTextColor ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const input = screen.getByRole( 'textbox', { name: 'Text' } );

		// Act.
		fireEvent.change( input, { target: { value: '#ff0000' } } );

		// Assert.
		expect( setTextColor ).toHaveBeenCalledWith( '#ff0000' );
	} );

	test( 'background color control calls setter when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setBgColor = jest.fn();
		setupEntityPropMock( {
			wc_clearance_badge_bg_color: [ '#FFEE85', setBgColor ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const input = screen.getByRole( 'textbox', { name: 'Background' } );

		// Act.
		fireEvent.change( input, { target: { value: '#0000ff' } } );

		// Assert.
		expect( setBgColor ).toHaveBeenCalledWith( '#0000ff' );
	} );

	test( 'border control calls setters when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setBorderColor = jest.fn();
		const setBorderStyle = jest.fn();
		const setBorderWidth = jest.fn();
		setupEntityPropMock( {
			wc_clearance_badge_border_color: [ '', setBorderColor ],
			wc_clearance_badge_border_style: [ 'none', setBorderStyle ],
			wc_clearance_badge_border_width: [ '0', setBorderWidth ],
		} );
		window.localStorage.setItem( 'wc_clearance_borders_enabled', '1' );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		window.localStorage.removeItem( 'wc_clearance_borders_enabled' );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const input = screen.getByRole( 'textbox', { name: 'Border' } );

		// Act.
		fireEvent.change( input, { target: { value: '2px' } } );

		// Assert.
		expect( setBorderWidth ).toHaveBeenCalledWith( '2px' );
	} );

	test( 'border control auto-applies solid style when width > 1 and style is not set', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setBorderStyle = jest.fn();
		setupEntityPropMock( {
			wc_clearance_badge_border_style: [ '', setBorderStyle ],
			wc_clearance_badge_border_width: [ '', jest.fn() ],
		} );
		window.localStorage.setItem( 'wc_clearance_borders_enabled', '1' );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		window.localStorage.removeItem( 'wc_clearance_borders_enabled' );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const input = screen.getByRole( 'textbox', { name: 'Border' } );

		// Act.
		fireEvent.change( input, { target: { value: '2px' } } );

		// Assert.
		expect( setBorderStyle ).toHaveBeenCalledWith( 'solid' );
	} );

	test( 'border control does not overwrite user-set style when width > 1', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setBorderStyle = jest.fn();
		setupEntityPropMock( {
			wc_clearance_badge_border_style: [ 'dashed', setBorderStyle ],
			wc_clearance_badge_border_width: [ '', jest.fn() ],
		} );
		window.localStorage.setItem( 'wc_clearance_borders_enabled', '1' );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		window.localStorage.removeItem( 'wc_clearance_borders_enabled' );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const input = screen.getByRole( 'textbox', { name: 'Border' } );

		// Act.
		fireEvent.change( input, { target: { value: '2px' } } );

		// Assert.
		expect( setBorderStyle ).not.toHaveBeenCalledWith( 'solid' );
	} );

	test( 'render function outputs the badge tab description', () => {
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
		expect(
			screen.getByText(
				'Customize the appearance of the clearance badge. Changes apply to the whole site.'
			)
		).toBeInTheDocument();
	} );

	test( 'render function outputs the badge tab', () => {
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
	} );

	test( 'border control is not visible when feature flag is disabled', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		setupEntityPropMock();
		// Do NOT set localStorage flag — bordersEnabled should be false.
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect(
			screen.queryByRole( 'textbox', { name: 'Border' } )
		).not.toBeInTheDocument();
	} );

	test( 'border radius control shows stored value', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		setupEntityPropMock( {
			wc_clearance_badge_border_radius: [ '4px', jest.fn() ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect( screen.getByRole( 'textbox', { name: 'Radius' } ) ).toHaveValue(
			'4px'
		);
	} );

	test( 'preview effect injects CSS vars into the document when called', () => {
		// Arrange.
		const mockUseEffect = jest.mocked(
			( jest.requireMock( '@wordpress/element' ) as {
				useEffect: jest.Mock;
			} ).useEffect
		);

		let capturedEffect: ( () => void ) | undefined;
		mockUseEffect.mockImplementationOnce( ( fn: () => void ) => {
			capturedEffect = fn;
		} );

		mockRegisterPlugin.mockClear();
		setupEntityPropMock( {
			wc_clearance_badge_label: [ 'Sale', jest.fn() ],
			wc_clearance_badge_bg_color: [ '#ff0000', jest.fn() ],
			wc_clearance_badge_text_color: [ '#ffffff', jest.fn() ],
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );

		// Act.
		capturedEffect?.();

		// Assert.
		const styleEl = document.getElementById( 'wc-clearance-preview-vars' );
		expect( styleEl ).not.toBeNull();
		expect( styleEl?.textContent ).toContain(
			'--wc-clearance-badge-label: "Sale"'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-clearance-badge-bg-color: #ff0000'
		);
		expect( styleEl?.textContent ).toContain(
			'--wc-clearance-badge-text-color: #ffffff'
		);

		// Cleanup.
		styleEl?.remove();
	} );
} );
