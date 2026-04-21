import type { ReactNode } from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { Edit } from '../edit';

jest.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: jest.fn( ( props ) => props ?? {} ),
	InspectorControls: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
	),
	PanelColorSettings: ( {
		colorSettings,
	}: {
		title: string;
		colorSettings: Array< {
			value: string | undefined;
			onChange: ( v: string | undefined ) => void;
			label: string;
		} >;
	} ) => (
		<div>
			{ colorSettings.map( ( setting ) => (
				<input
					key={ setting.label }
					aria-label={ setting.label }
					type="color"
					value={ setting.value ?? '' }
					onChange={ ( e ) => setting.onChange( e.target.value ) }
				/>
			) ) }
		</div>
	),
	RichText: ( {
		value,
		onChange,
		placeholder,
	}: {
		value: string;
		onChange: ( v: string ) => void;
		placeholder?: string;
		[ key: string ]: unknown;
	} ) => (
		<input
			type="text"
			value={ value }
			placeholder={ placeholder }
			onChange={ ( e ) => onChange( e.target.value ) }
		/>
	),
} ) );

jest.mock( '@wordpress/components', () => ( {
	BaseControl: ( { children }: { children: ReactNode; label?: string } ) => (
		<div>{ children }</div>
	),
	PanelBody: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
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
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( str: string ) => str ),
} ) );

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

import { useEntityProp } from '@wordpress/core-data';

const mockUseEntityProp = useEntityProp as jest.Mock;

describe( 'Edit', () => {
	test( 'renders badge with default label when setting is empty', () => {
		// Arrange.
		const setLabel = jest.fn();
		const setBgColor = jest.fn();
		const setTextColor = jest.fn();
		mockUseEntityProp
			.mockReturnValueOnce( [ undefined, setLabel, undefined ] )
			.mockReturnValueOnce( [ undefined, setBgColor, undefined ] )
			.mockReturnValueOnce( [ undefined, setTextColor, undefined ] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByDisplayValue( 'Clearance' ) ).toBeInTheDocument();
	} );

	test( 'renders badge with label from global setting', () => {
		// Arrange.
		const setLabel = jest.fn();
		const setBgColor = jest.fn();
		const setTextColor = jest.fn();
		mockUseEntityProp
			.mockReturnValueOnce( [ 'Sale', setLabel, undefined ] )
			.mockReturnValueOnce( [ undefined, setBgColor, undefined ] )
			.mockReturnValueOnce( [ undefined, setTextColor, undefined ] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByDisplayValue( 'Sale' ) ).toBeInTheDocument();
	} );

	test( 'calls setLabel with updated label when content changes', () => {
		// Arrange.
		const setLabel = jest.fn();
		const setBgColor = jest.fn();
		const setTextColor = jest.fn();
		mockUseEntityProp
			.mockReturnValueOnce( [ 'Clearance', setLabel, undefined ] )
			.mockReturnValueOnce( [ undefined, setBgColor, undefined ] )
			.mockReturnValueOnce( [ undefined, setTextColor, undefined ] );
		render( <Edit /> );
		const input = screen.getByDisplayValue( 'Clearance' );

		// Act.
		fireEvent.change( input, { target: { value: 'Discounted' } } );

		// Assert.
		expect( setLabel ).toHaveBeenCalledWith( 'Discounted' );
	} );

	test( 'renders background color from global setting', () => {
		// Arrange.
		const setLabel = jest.fn();
		const setBgColor = jest.fn();
		const setTextColor = jest.fn();
		mockUseEntityProp
			.mockReturnValueOnce( [ 'Clearance', setLabel, undefined ] )
			.mockReturnValueOnce( [ '#FFEE85', setBgColor, undefined ] )
			.mockReturnValueOnce( [ '#222222', setTextColor, undefined ] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByLabelText( 'Background color' ) ).toHaveValue(
			'#ffee85'
		);
	} );

	test( 'renders text color from global setting', () => {
		// Arrange.
		const setLabel = jest.fn();
		const setBgColor = jest.fn();
		const setTextColor = jest.fn();
		mockUseEntityProp
			.mockReturnValueOnce( [ 'Clearance', setLabel, undefined ] )
			.mockReturnValueOnce( [ '#FFEE85', setBgColor, undefined ] )
			.mockReturnValueOnce( [ '#222222', setTextColor, undefined ] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByLabelText( 'Text color' ) ).toHaveValue(
			'#222222'
		);
	} );

	test( 'calls setBgColor with updated value when background color changes', () => {
		// Arrange.
		const setLabel = jest.fn();
		const setBgColor = jest.fn();
		const setTextColor = jest.fn();
		mockUseEntityProp
			.mockReturnValueOnce( [ 'Clearance', setLabel, undefined ] )
			.mockReturnValueOnce( [ '#FFEE85', setBgColor, undefined ] )
			.mockReturnValueOnce( [ '#222222', setTextColor, undefined ] );
		render( <Edit /> );
		const colorInput = screen.getByLabelText( 'Background color' );

		// Act.
		fireEvent.change( colorInput, { target: { value: '#FF0000' } } );

		// Assert.
		expect( setBgColor ).toHaveBeenCalledWith( '#ff0000' );
	} );

	test( 'calls setTextColor with updated value when text color changes', () => {
		// Arrange.
		const setLabel = jest.fn();
		const setBgColor = jest.fn();
		const setTextColor = jest.fn();
		mockUseEntityProp
			.mockReturnValueOnce( [ 'Clearance', setLabel, undefined ] )
			.mockReturnValueOnce( [ '#FFEE85', setBgColor, undefined ] )
			.mockReturnValueOnce( [ '#222222', setTextColor, undefined ] );
		render( <Edit /> );
		const colorInput = screen.getByLabelText( 'Text color' );

		// Act.
		fireEvent.change( colorInput, { target: { value: '#0000FF' } } );

		// Assert.
		expect( setTextColor ).toHaveBeenCalledWith( '#0000ff' );
	} );
} );
