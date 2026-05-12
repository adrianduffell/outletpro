import type { ReactNode } from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { registerPlugin } from '@wordpress/plugins';
import useSettings from '../../use-settings';
import { useSelect } from '@wordpress/data';
import { TabPanel } from '@wordpress/components';

jest.mock( '@wordpress/plugins', () => ( {
	registerPlugin: jest.fn(),
} ) );

jest.mock( '@wordpress/data', () => ( {
	useSelect: jest.fn(),
} ) );

jest.mock( '@wordpress/core-data', () => ( {
	store: {},
} ) );

jest.mock( '../../use-settings', () => jest.fn() );

jest.mock( '@wordpress/element', () => ( {
	...jest.requireActual( '@wordpress/element' ),
	useMemo: jest.fn( ( fn: () => unknown ) => fn() ),
} ) );

jest.mock( '@wordpress/components', () => ( {
	BaseControl: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
	),
	TabPanel: jest.fn(
		( {
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
		)
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
	RangeControl: ( {
		label,
		value,
		onChange,
		min,
		max,
		step,
	}: {
		label: string;
		value?: number;
		onChange: ( v: number | undefined ) => void;
		min?: number;
		max?: number;
		step?: number;
	} ) => (
		<input
			type="range"
			aria-label={ label }
			value={ value }
			min={ min }
			max={ max }
			step={ step }
			onChange={ ( e ) => onChange( Number( e.target.value ) ) }
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
	TextareaControl: ( {
		label,
		value,
		onChange,
	}: {
		label: string;
		value: string;
		onChange: ( v: string ) => void;
	} ) => (
		<textarea
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
const mockUseSettings = useSettings as jest.Mock;
const mockUseSelect = useSelect as jest.Mock;
const mockTabPanel = TabPanel as unknown as jest.Mock;

const createDefaultSettings = () => ( {
	label: undefined,
	setLabel: jest.fn(),
	textColor: undefined,
	setTextColor: jest.fn(),
	bgColor: undefined,
	setBgColor: jest.fn(),
	fontSize: undefined,
	setFontSize: jest.fn(),
	fontWeight: undefined,
	setFontWeight: jest.fn(),
	borderColor: undefined,
	setBorderColor: jest.fn(),
	borderStyle: undefined,
	setBorderStyle: jest.fn(),
	borderWidth: undefined,
	setBorderWidth: jest.fn(),
	borderRadius: undefined,
	setBorderRadius: jest.fn(),
	paddingTop: undefined,
	setPaddingTop: jest.fn(),
	paddingRight: undefined,
	setPaddingRight: jest.fn(),
	paddingBottom: undefined,
	setPaddingBottom: jest.fn(),
	paddingLeft: undefined,
	setPaddingLeft: jest.fn(),
	scale: undefined,
	setScale: jest.fn(),
	density: undefined,
	setDensity: jest.fn(),
	message: undefined,
	setMessage: jest.fn(),
} );

describe( 'page-editor-sidebar registration', () => {
	test( 'registers the sidebar plugin with expected name and render function', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( { ...createDefaultSettings() } );

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

	test( 'does not register plugin on post.php', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const originalPathname = window.location.pathname;
		window.history.pushState( {}, '', '/wp-admin/post.php' );

		// Act.
		jest.isolateModules( () => {
			require( '../index' );
		} );

		// Assert.
		expect( mockRegisterPlugin ).not.toHaveBeenCalledWith(
			'wc-clearance-sidebar',
			expect.anything()
		);

		// Cleanup.
		window.history.pushState( {}, '', originalPathname );
	} );

	test( 'does not register plugin on post-new.php', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const originalPathname = window.location.pathname;
		window.history.pushState( {}, '', '/wp-admin/post-new.php' );

		// Act.
		jest.isolateModules( () => {
			require( '../index' );
		} );

		// Assert.
		expect( mockRegisterPlugin ).not.toHaveBeenCalledWith(
			'wc-clearance-sidebar',
			expect.anything()
		);

		// Cleanup.
		window.history.pushState( {}, '', originalPathname );
	} );

	test( 'render function outputs the sidebar title', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( { ...createDefaultSettings() } );
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( { ...createDefaultSettings() } );
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			label: 'Sale',
			setLabel,
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			label: undefined,
			setLabel: jest.fn(),
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			label: 'Clearance',
			setLabel,
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			fontSize: '1rem',
			setFontSize: jest.fn(),
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

	test( 'font scale control reflects stored scale value', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			scale: 140,
			setScale: jest.fn(),
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect( screen.getByRole( 'slider', { name: 'Scale' } ) ).toHaveValue(
			'140'
		);
	} );

	test( 'font scale control allows undefined scale value', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setScale = jest.fn();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			scale: undefined,
			setScale,
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );
		const input = screen.getByRole( 'slider', { name: 'Scale' } );

		// Assert.
		expect( input ).toHaveAttribute( 'value', '' );
		expect( setScale ).not.toHaveBeenCalled();
	} );

	test( 'font scale control calls setter when changed from undefined', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setScale = jest.fn();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			scale: undefined,
			setScale,
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const input = screen.getByRole( 'slider', { name: 'Scale' } );

		// Act.
		fireEvent.change( input, { target: { value: '130' } } );

		// Assert.
		expect( setScale ).toHaveBeenCalledWith( 130 );
	} );

	test( 'font scale control uses accepted range and step', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			scale: 120,
			setScale: jest.fn(),
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );
		const input = screen.getByRole( 'slider', { name: 'Scale' } );

		// Assert.
		expect( input ).toHaveAttribute( 'min', '50' );
		expect( input ).toHaveAttribute( 'max', '200' );
		expect( input ).toHaveAttribute( 'step', '5' );
	} );

	test( 'font weight control calls setter when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setFontWeight = jest.fn();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			fontWeight: '',
			setFontWeight,
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			textColor: '#ff0000',
			setTextColor: jest.fn(),
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			bgColor: undefined,
			setBgColor: jest.fn(),
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			borderRadius: '2px',
			setBorderRadius,
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			paddingTop: '5px',
			setPaddingTop,
			paddingRight: '5px',
			setPaddingRight,
			paddingBottom: '5px',
			setPaddingBottom,
			paddingLeft: '5px',
			setPaddingLeft,
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			fontSize: '0.875rem',
			setFontSize,
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

	test( 'font scale control calls scale setter when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setScale = jest.fn();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			scale: 120,
			setScale,
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		render( pluginConfig.render() );
		const input = screen.getByRole( 'slider', { name: 'Scale' } );

		// Act.
		fireEvent.change( input, { target: { value: '130' } } );

		// Assert.
		expect( setScale ).toHaveBeenCalledWith( 130 );
	} );

	test( 'text color control calls setter when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setTextColor = jest.fn();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			textColor: '#222',
			setTextColor,
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			bgColor: '#FFEE85',
			setBgColor,
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			borderColor: '',
			setBorderColor,
			borderStyle: 'none',
			setBorderStyle,
			borderWidth: '0',
			setBorderWidth,
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			borderStyle: '',
			setBorderStyle,
			borderWidth: '',
			setBorderWidth: jest.fn(),
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			borderStyle: 'dashed',
			setBorderStyle,
			borderWidth: '',
			setBorderWidth: jest.fn(),
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( { ...createDefaultSettings() } );
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( { ...createDefaultSettings() } );
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( { ...createDefaultSettings() } );
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
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			borderRadius: '4px',
			setBorderRadius: jest.fn(),
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

	test( 'render function outputs the message tab', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( { ...createDefaultSettings() } );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect(
			screen.getByRole( 'tab', { name: 'Message' } )
		).toBeInTheDocument();
	} );

	test( 'render function outputs the message tab description', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( { ...createDefaultSettings() } );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		mockTabPanel.mockImplementationOnce(
			( {
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
					{ children( tabs[ 1 ] ) }
				</section>
			)
		);

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect(
			screen.getByTestId( 'wc-clearance-message-tab-description' )
		).toBeInTheDocument();
	} );

	test( 'message textarea shows stored value', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setMessage = jest.fn();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			message: 'Only while stocks last',
			setMessage,
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		mockTabPanel.mockImplementationOnce(
			( {
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
					{ children( tabs[ 1 ] ) }
				</section>
			)
		);

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect(
			screen.getByRole( 'textbox', { name: 'Message' } )
		).toHaveValue( 'Only while stocks last' );
	} );

	test( 'message textarea is empty when setting is not set', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			message: undefined,
			setMessage: jest.fn(),
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		mockTabPanel.mockImplementationOnce(
			( {
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
					{ children( tabs[ 1 ] ) }
				</section>
			)
		);

		// Act.
		render( pluginConfig.render() );

		// Assert.
		expect(
			screen.getByRole( 'textbox', { name: 'Message' } )
		).toHaveValue( '' );
	} );

	test( 'message textarea calls setter when changed', () => {
		// Arrange.
		mockRegisterPlugin.mockClear();
		const setMessage = jest.fn();
		mockUseSelect.mockReturnValue( true );
		mockUseSettings.mockReturnValue( {
			...createDefaultSettings(),
			message: 'Only while stocks last',
			setMessage,
		} );
		jest.isolateModules( () => {
			require( '../index' );
		} );
		const [ , pluginConfig ] = mockRegisterPlugin.mock.calls[ 0 ];
		mockTabPanel.mockImplementationOnce(
			( {
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
					{ children( tabs[ 1 ] ) }
				</section>
			)
		);
		render( pluginConfig.render() );
		const textarea = screen.getByRole( 'textbox', { name: 'Message' } );

		// Act.
		fireEvent.change( textarea, {
			target: { value: 'Only while supplies last' },
		} );

		// Assert.
		expect( setMessage ).toHaveBeenCalledWith( 'Only while supplies last' );
	} );
} );
