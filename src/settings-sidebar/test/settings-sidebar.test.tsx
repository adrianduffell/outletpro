import type { ReactNode } from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { SettingsSidebar, withSiteRecord } from '../index';
import useSettings from '../../use-settings';
import { useSelect } from '@wordpress/data';
import { TabPanel } from '@wordpress/components';

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

	PluginSidebarMoreMenuItem: () => null,
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( text: string ) => text ),
} ) );

const mockUseSettings = useSettings as jest.Mock;
const mockUseSelect = useSelect as jest.Mock;
const mockTabPanel = TabPanel as unknown as jest.Mock;

const createInitialSettings = () => ( {
	label: undefined,
	setLabel: jest.fn(),
	textColor: undefined,
	setTextColor: jest.fn(),
	bgColor: undefined,
	setBgColor: jest.fn(),
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
	scale: undefined,
	setScale: jest.fn(),
	density: undefined,
	setDensity: jest.fn(),
	message: undefined,
	setMessage: jest.fn(),
} );

describe( 'settings-sidebar registration', () => {
	test( 'render function renders nothing when site record is not available', () => {
		// Arrange.
		mockUseSelect.mockReturnValue( false );
		const WrappedSidebar = withSiteRecord( SettingsSidebar );

		// Act.
		const { container } = render( <WrappedSidebar /> );

		// Assert.
		expect( container ).toBeEmptyDOMElement();
	} );

	test( 'render function outputs the sidebar title', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( { ...createInitialSettings() } );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect( screen.getByText( 'Outlet settings' ) ).toBeInTheDocument();
	} );

	test( 'render function outputs all panel sections', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( { ...createInitialSettings() } );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect( screen.getByText( 'Label' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Typography' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Color' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Dimensions' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Border' ) ).toBeInTheDocument();
	} );

	test( 'badge label control shows stored value', () => {
		// Arrange.
		const setLabel = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			label: 'Sale',
			setLabel,
		} );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect( screen.getByRole( 'textbox', { name: 'Label' } ) ).toHaveValue(
			'Sale'
		);
	} );

	test( 'badge label control is empty when setting is empty', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			label: undefined,
			setLabel: jest.fn(),
		} );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect( screen.getByRole( 'textbox', { name: 'Label' } ) ).toHaveValue(
			''
		);
	} );

	test( 'badge label control calls setter when changed', () => {
		// Arrange.
		const setLabel = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			label: 'Clearance',
			setLabel,
		} );
		render( <SettingsSidebar /> );
		const input = screen.getByRole( 'textbox', { name: 'Label' } );

		// Act.
		fireEvent.change( input, { target: { value: 'Discounts' } } );

		// Assert.
		expect( setLabel ).toHaveBeenCalledWith( 'Discounts' );
	} );

	test( 'font scale control reflects stored scale value', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			scale: 140,
			setScale: jest.fn(),
		} );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect( screen.getByRole( 'slider', { name: 'Scale' } ) ).toHaveValue(
			'140'
		);
	} );

	test( 'font scale control allows undefined scale value', () => {
		// Arrange.
		const setScale = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			scale: undefined,
			setScale,
		} );

		// Act.
		render( <SettingsSidebar /> );
		const input = screen.getByRole( 'slider', { name: 'Scale' } );

		// Assert.
		expect( input ).toHaveAttribute( 'value', '' );
		expect( setScale ).not.toHaveBeenCalled();
	} );

	test( 'font scale control calls setter when changed from undefined', () => {
		// Arrange.
		const setScale = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			scale: undefined,
			setScale,
		} );
		render( <SettingsSidebar /> );
		const input = screen.getByRole( 'slider', { name: 'Scale' } );

		// Act.
		fireEvent.change( input, { target: { value: '130' } } );

		// Assert.
		expect( setScale ).toHaveBeenCalledWith( 130 );
	} );

	test( 'font scale control uses accepted range and step', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			scale: 120,
			setScale: jest.fn(),
		} );

		// Act.
		render( <SettingsSidebar /> );
		const input = screen.getByRole( 'slider', { name: 'Scale' } );

		// Assert.
		expect( input ).toHaveAttribute( 'min', '100' );
		expect( input ).toHaveAttribute( 'max', '200' );
		expect( input ).toHaveAttribute( 'step', '1' );
	} );

	test( 'font weight control calls setter when changed', () => {
		// Arrange.
		const setFontWeight = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			fontWeight: '',
			setFontWeight,
		} );
		render( <SettingsSidebar /> );
		const select = screen.getByRole( 'combobox', { name: 'Font weight' } );

		// Act.
		fireEvent.change( select, { target: { value: '700' } } );

		// Assert.
		expect( setFontWeight ).toHaveBeenCalledWith( '700' );
	} );

	test( 'text color control shows stored value', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			textColor: '#ff0000',
			setTextColor: jest.fn(),
		} );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect( screen.getByRole( 'textbox', { name: 'Text' } ) ).toHaveValue(
			'#ff0000'
		);
	} );

	test( 'background color control is empty when setting is empty', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			bgColor: undefined,
			setBgColor: jest.fn(),
		} );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect(
			screen.getByRole( 'textbox', { name: 'Background' } )
		).toHaveValue( '' );
	} );

	test( 'border radius control calls setter when changed', () => {
		// Arrange.
		const setBorderRadius = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			borderRadius: '2px',
			setBorderRadius,
		} );
		render( <SettingsSidebar /> );
		const input = screen.getByRole( 'textbox', {
			name: 'Radius',
		} );

		// Act.
		fireEvent.change( input, { target: { value: '8px' } } );

		// Assert.
		expect( setBorderRadius ).toHaveBeenCalledWith( '8px' );
	} );

	test( 'font scale control calls scale setter when changed', () => {
		// Arrange.
		const setScale = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			scale: 120,
			setScale,
		} );
		render( <SettingsSidebar /> );
		const input = screen.getByRole( 'slider', { name: 'Scale' } );

		// Act.
		fireEvent.change( input, { target: { value: '130' } } );

		// Assert.
		expect( setScale ).toHaveBeenCalledWith( 130 );
	} );

	test( 'density control reflects stored density value', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			density: 60,
			setDensity: jest.fn(),
		} );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect(
			screen.getByRole( 'slider', { name: 'Font size' } )
		).toHaveValue( '60' );
	} );

	test( 'density control allows undefined density value', () => {
		// Arrange.
		const setDensity = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			density: undefined,
			setDensity,
		} );

		// Act.
		render( <SettingsSidebar /> );
		const input = screen.getByRole( 'slider', { name: 'Font size' } );

		// Assert.
		expect( input ).toHaveAttribute( 'value', '' );
		expect( setDensity ).not.toHaveBeenCalled();
	} );

	test( 'density control calls setter when changed', () => {
		// Arrange.
		const setDensity = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			density: 60,
			setDensity,
		} );
		render( <SettingsSidebar /> );
		const input = screen.getByRole( 'slider', { name: 'Font size' } );

		// Act.
		fireEvent.change( input, { target: { value: '75' } } );

		// Assert.
		expect( setDensity ).toHaveBeenCalledWith( 75 );
	} );

	test( 'density control uses accepted range and step', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			density: 60,
			setDensity: jest.fn(),
		} );

		// Act.
		render( <SettingsSidebar /> );
		const input = screen.getByRole( 'slider', { name: 'Font size' } );

		// Assert.
		expect( input ).toHaveAttribute( 'min', '0' );
		expect( input ).toHaveAttribute( 'max', '100' );
		expect( input ).toHaveAttribute( 'step', '1' );
	} );

	test( 'text color control calls setter when changed', () => {
		// Arrange.
		const setTextColor = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			textColor: '#222',
			setTextColor,
		} );
		render( <SettingsSidebar /> );
		const input = screen.getByRole( 'textbox', { name: 'Text' } );

		// Act.
		fireEvent.change( input, { target: { value: '#ff0000' } } );

		// Assert.
		expect( setTextColor ).toHaveBeenCalledWith( '#ff0000' );
	} );

	test( 'background color control calls setter when changed', () => {
		// Arrange.
		const setBgColor = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			bgColor: '#FFEE85',
			setBgColor,
		} );
		render( <SettingsSidebar /> );
		const input = screen.getByRole( 'textbox', { name: 'Background' } );

		// Act.
		fireEvent.change( input, { target: { value: '#0000ff' } } );

		// Assert.
		expect( setBgColor ).toHaveBeenCalledWith( '#0000ff' );
	} );

	test( 'border control calls setters when changed', () => {
		// Arrange.
		const setBorderColor = jest.fn();
		const setBorderStyle = jest.fn();
		const setBorderWidth = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			borderColor: '',
			setBorderColor,
			borderStyle: 'none',
			setBorderStyle,
			borderWidth: '0',
			setBorderWidth,
		} );
		window.localStorage.setItem( 'outletpro_borders_enabled', '1' );
		render( <SettingsSidebar /> );
		window.localStorage.removeItem( 'outletpro_borders_enabled' );
		const input = screen.getByRole( 'textbox', { name: 'Border' } );

		// Act.
		fireEvent.change( input, { target: { value: '2px' } } );

		// Assert.
		expect( setBorderWidth ).toHaveBeenCalledWith( '2px' );
	} );

	test( 'border control auto-applies solid style when width > 1 and style is not set', () => {
		// Arrange.
		const setBorderStyle = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			borderStyle: '',
			setBorderStyle,
			borderWidth: '',
			setBorderWidth: jest.fn(),
		} );
		window.localStorage.setItem( 'outletpro_borders_enabled', '1' );
		render( <SettingsSidebar /> );
		window.localStorage.removeItem( 'outletpro_borders_enabled' );
		const input = screen.getByRole( 'textbox', { name: 'Border' } );

		// Act.
		fireEvent.change( input, { target: { value: '2px' } } );

		// Assert.
		expect( setBorderStyle ).toHaveBeenCalledWith( 'solid' );
	} );

	test( 'border control does not overwrite user-set style when width > 1', () => {
		// Arrange.
		const setBorderStyle = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			borderStyle: 'dashed',
			setBorderStyle,
			borderWidth: '',
			setBorderWidth: jest.fn(),
		} );
		window.localStorage.setItem( 'outletpro_borders_enabled', '1' );
		render( <SettingsSidebar /> );
		window.localStorage.removeItem( 'outletpro_borders_enabled' );
		const input = screen.getByRole( 'textbox', { name: 'Border' } );

		// Act.
		fireEvent.change( input, { target: { value: '2px' } } );

		// Assert.
		expect( setBorderStyle ).not.toHaveBeenCalledWith( 'solid' );
	} );

	test( 'render function outputs the badge tab description', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( { ...createInitialSettings() } );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect(
			screen.getByText(
				'Customize the appearance of the outlet badge. Changes apply to the whole site.'
			)
		).toBeInTheDocument();
	} );

	test( 'render function outputs the badge tab', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( { ...createInitialSettings() } );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect( screen.getByText( 'Badge' ) ).toBeInTheDocument();
	} );

	test( 'border control is not visible when feature flag is disabled', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( { ...createInitialSettings() } );
		// Do NOT set localStorage flag — bordersEnabled should be false.

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect(
			screen.queryByRole( 'textbox', { name: 'Border' } )
		).not.toBeInTheDocument();
	} );

	test( 'border radius control shows stored value', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			borderRadius: '4px',
			setBorderRadius: jest.fn(),
		} );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect( screen.getByRole( 'textbox', { name: 'Radius' } ) ).toHaveValue(
			'4px'
		);
	} );

	test( 'render function outputs the message tab', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( { ...createInitialSettings() } );

		// Act.
		render( <SettingsSidebar /> );

		// Assert.
		expect(
			screen.getByRole( 'tab', { name: 'Message' } )
		).toBeInTheDocument();
	} );

	test( 'render function outputs the message tab description', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( { ...createInitialSettings() } );
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
		render( <SettingsSidebar /> );

		// Assert.
		expect(
			screen.getByTestId( 'outletpro-message-tab-description' )
		).toBeInTheDocument();
	} );

	test( 'message textarea shows stored value', () => {
		// Arrange.
		const setMessage = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			message: 'Only while stocks last',
			setMessage,
		} );
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
		render( <SettingsSidebar /> );

		// Assert.
		expect(
			screen.getByRole( 'textbox', { name: 'Message' } )
		).toHaveValue( 'Only while stocks last' );
	} );

	test( 'message textarea is empty when setting is not set', () => {
		// Arrange.
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			message: undefined,
			setMessage: jest.fn(),
		} );
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
		render( <SettingsSidebar /> );

		// Assert.
		expect(
			screen.getByRole( 'textbox', { name: 'Message' } )
		).toHaveValue( '' );
	} );

	test( 'message textarea calls setter when changed', () => {
		// Arrange.
		const setMessage = jest.fn();
		mockUseSettings.mockReturnValue( {
			...createInitialSettings(),
			message: 'Only while stocks last',
			setMessage,
		} );
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
		render( <SettingsSidebar /> );
		const textarea = screen.getByRole( 'textbox', { name: 'Message' } );

		// Act.
		fireEvent.change( textarea, {
			target: { value: 'Only while supplies last' },
		} );

		// Assert.
		expect( setMessage ).toHaveBeenCalledWith( 'Only while supplies last' );
	} );
} );
