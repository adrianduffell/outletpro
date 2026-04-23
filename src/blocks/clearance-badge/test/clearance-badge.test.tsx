import type { ReactNode } from 'react';
import { render, screen, fireEvent, act } from '@testing-library/react';
import { Edit } from '../edit';

jest.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: jest.fn( ( props ) => props ?? {} ),
	InspectorControls: ( { children }: { children: ReactNode } ) => (
		<div>{ children }</div>
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

const defaultAttributes = { style: undefined };

function mockEntityProps( {
	label = 'Clearance',
	bgColor = '#FFEE85',
	textColor = '#222',
	setLabel = jest.fn(),
	setBgColor = jest.fn(),
	setTextColor = jest.fn(),
} = {} ) {
	mockUseEntityProp
		.mockReturnValueOnce( [ label, setLabel, undefined ] )
		.mockReturnValueOnce( [ bgColor, setBgColor, undefined ] )
		.mockReturnValueOnce( [ textColor, setTextColor, undefined ] );
}

describe( 'Edit', () => {
	test( 'renders badge with default label when setting is empty', () => {
		// Arrange.
		const setLabel = jest.fn();
		mockUseEntityProp
			.mockReturnValueOnce( [ undefined, setLabel, undefined ] )
			.mockReturnValueOnce( [ undefined, jest.fn(), undefined ] )
			.mockReturnValueOnce( [ undefined, jest.fn(), undefined ] );

		// Act.
		render(
			<Edit
				attributes={ defaultAttributes }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		expect( screen.getByDisplayValue( 'Clearance' ) ).toBeInTheDocument();
	} );

	test( 'renders badge with label from global setting', () => {
		// Arrange.
		mockEntityProps( { label: 'Sale' } );

		// Act.
		render(
			<Edit
				attributes={ defaultAttributes }
				setAttributes={ jest.fn() }
			/>
		);

		// Assert.
		expect( screen.getByDisplayValue( 'Sale' ) ).toBeInTheDocument();
	} );

	test( 'calls setLabel with updated label when content changes', () => {
		// Arrange.
		const setLabel = jest.fn();
		mockEntityProps( { label: 'Clearance', setLabel } );
		render(
			<Edit
				attributes={ defaultAttributes }
				setAttributes={ jest.fn() }
			/>
		);
		const input = screen.getByDisplayValue( 'Clearance' );

		// Act.
		fireEvent.change( input, { target: { value: 'Discounted' } } );

		// Assert.
		expect( setLabel ).toHaveBeenCalledWith( 'Discounted' );
	} );

	test( 'sets block color attributes from global settings on initial load', async () => {
		// Arrange.
		const setAttributes = jest.fn();
		mockEntityProps( { bgColor: '#FFEE85', textColor: '#222' } );

		// Act.
		await act( async () => {
			render(
				<Edit
					attributes={ defaultAttributes }
					setAttributes={ setAttributes }
				/>
			);
		} );

		// Assert.
		expect( setAttributes ).toHaveBeenCalledWith(
			expect.objectContaining( {
				style: expect.objectContaining( {
					color: expect.objectContaining( {
						background: '#FFEE85',
						text: '#222',
					} ),
				} ),
			} )
		);
	} );

	test( 'does not seed colors when global settings are not yet loaded', async () => {
		// Arrange.
		const setAttributes = jest.fn();
		mockUseEntityProp
			.mockReturnValueOnce( [ 'Clearance', jest.fn(), undefined ] )
			.mockReturnValueOnce( [ undefined, jest.fn(), undefined ] )
			.mockReturnValueOnce( [ undefined, jest.fn(), undefined ] );

		// Act.
		await act( async () => {
			render(
				<Edit
					attributes={ defaultAttributes }
					setAttributes={ setAttributes }
				/>
			);
		} );

		// Assert.
		expect( setAttributes ).not.toHaveBeenCalled();
	} );

	test( 'syncs changed block bg color back to global setting', async () => {
		// Arrange.
		const setBgColor = jest.fn();
		mockEntityProps( { bgColor: '#FFEE85', setBgColor } );
		const { rerender } = await act( async () =>
			render(
				<Edit
					attributes={ defaultAttributes }
					setAttributes={ jest.fn() }
				/>
			)
		);

		// After seeding, simulate user picking a new color via the native picker.
		mockEntityProps( { bgColor: '#FFEE85', setBgColor } );

		// Act.
		await act( async () => {
			rerender(
				<Edit
					attributes={ {
						style: {
							color: { background: '#FF0000', text: '#222' },
						},
					} }
					setAttributes={ jest.fn() }
				/>
			);
		} );

		// Assert.
		expect( setBgColor ).toHaveBeenCalledWith( '#FF0000' );
	} );

	test( 'syncs changed block text color back to global setting', async () => {
		// Arrange.
		const setTextColor = jest.fn();
		mockEntityProps( { textColor: '#222', setTextColor } );
		const { rerender } = await act( async () =>
			render(
				<Edit
					attributes={ defaultAttributes }
					setAttributes={ jest.fn() }
				/>
			)
		);

		// After seeding, simulate user picking a new color via the native picker.
		mockEntityProps( { textColor: '#222', setTextColor } );

		// Act.
		await act( async () => {
			rerender(
				<Edit
					attributes={ {
						style: {
							color: { background: '#FFEE85', text: '#000000' },
						},
					} }
					setAttributes={ jest.fn() }
				/>
			);
		} );

		// Assert.
		expect( setTextColor ).toHaveBeenCalledWith( '#000000' );
	} );
} );
