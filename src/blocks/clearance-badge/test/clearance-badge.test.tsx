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
			{ colorSettings.map( ( { label, value, onChange } ) => (
				<input
					key={ label }
					aria-label={ label }
					value={ value ?? '' }
					onChange={ ( e ) => onChange( e.target.value ) }
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

function mockEntityProps( {
	label = 'Clearance',
	bgColor = '#FFEE85',
	textColor = '#222',
}: {
	label?: string | undefined;
	bgColor?: string | undefined;
	textColor?: string | undefined;
} = {} ) {
	const setLabel = jest.fn();
	const setBgColor = jest.fn();
	const setTextColor = jest.fn();

	mockUseEntityProp.mockImplementation(
		( _kind: string, _name: string, key: string ) => {
			if ( key === 'wc_clearance_badge_label' ) {
				return [ label, setLabel, undefined ];
			}
			if ( key === 'wc_clearance_badge_bg_color' ) {
				return [ bgColor, setBgColor, undefined ];
			}
			if ( key === 'wc_clearance_badge_text_color' ) {
				return [ textColor, setTextColor, undefined ];
			}
			return [ undefined, jest.fn(), undefined ];
		}
	);

	return { setLabel, setBgColor, setTextColor };
}

describe( 'Edit', () => {
	test( 'renders badge with default label when setting is empty', () => {
		// Arrange.
		mockEntityProps( { label: undefined } );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByDisplayValue( 'Clearance' ) ).toBeInTheDocument();
	} );

	test( 'renders badge with label from global setting', () => {
		// Arrange.
		mockEntityProps( { label: 'Sale' } );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByDisplayValue( 'Sale' ) ).toBeInTheDocument();
	} );

	test( 'calls setLabel with updated label when content changes', () => {
		// Arrange.
		const { setLabel } = mockEntityProps( { label: 'Clearance' } );
		render( <Edit /> );
		const input = screen.getByDisplayValue( 'Clearance' );

		// Act.
		fireEvent.change( input, { target: { value: 'Discounted' } } );

		// Assert.
		expect( setLabel ).toHaveBeenCalledWith( 'Discounted' );
	} );

	test( 'renders background color picker with value from global setting', () => {
		// Arrange.
		mockEntityProps( { bgColor: '#FF0000' } );

		// Act.
		render( <Edit /> );

		// Assert.
		expect(
			screen.getByRole( 'textbox', { name: 'Background color' } )
		).toHaveValue( '#FF0000' );
	} );

	test( 'renders text color picker with value from global setting', () => {
		// Arrange.
		mockEntityProps( { textColor: '#0000FF' } );

		// Act.
		render( <Edit /> );

		// Assert.
		expect(
			screen.getByRole( 'textbox', { name: 'Text color' } )
		).toHaveValue( '#0000FF' );
	} );

	test( 'calls setBgColor when background color changes', () => {
		// Arrange.
		const { setBgColor } = mockEntityProps( { bgColor: '#FF0000' } );
		render( <Edit /> );
		const input = screen.getByRole( 'textbox', {
			name: 'Background color',
		} );

		// Act.
		fireEvent.change( input, { target: { value: '#00FF00' } } );

		// Assert.
		expect( setBgColor ).toHaveBeenCalledWith( '#00FF00' );
	} );

	test( 'calls setTextColor when text color changes', () => {
		// Arrange.
		const { setTextColor } = mockEntityProps( { textColor: '#222' } );
		render( <Edit /> );
		const input = screen.getByRole( 'textbox', { name: 'Text color' } );

		// Act.
		fireEvent.change( input, { target: { value: '#000000' } } );

		// Assert.
		expect( setTextColor ).toHaveBeenCalledWith( '#000000' );
	} );
} );
