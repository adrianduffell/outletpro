import { render, screen, fireEvent, act } from '@testing-library/react';
import { Edit } from '../edit';

jest.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: jest.fn( ( props ) => props ?? {} ),
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

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( str: string ) => str ),
} ) );

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

import { useEntityProp } from '@wordpress/core-data';

const mockUseEntityProp = useEntityProp as jest.Mock;

function makeProps( overrides: object = {} ) {
	return {
		attributes: { style: {} },
		setAttributes: jest.fn(),
		...overrides,
	};
}

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
		render( <Edit { ...makeProps() } /> );

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
		render( <Edit { ...makeProps() } /> );

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
		render( <Edit { ...makeProps() } /> );
		const input = screen.getByDisplayValue( 'Clearance' );

		// Act.
		fireEvent.change( input, { target: { value: 'Discounted' } } );

		// Assert.
		expect( setLabel ).toHaveBeenCalledWith( 'Discounted' );
	} );

	test( 'seeds block style.color from global settings on first load', () => {
		// Arrange.
		const setLabel = jest.fn();
		const setBgColor = jest.fn();
		const setTextColor = jest.fn();
		const setAttributes = jest.fn();
		mockUseEntityProp
			.mockReturnValueOnce( [ 'Clearance', setLabel, undefined ] )
			.mockReturnValueOnce( [ '#FFEE85', setBgColor, undefined ] )
			.mockReturnValueOnce( [ '#222222', setTextColor, undefined ] );

		// Act.
		act( () => {
			render( <Edit { ...makeProps( { setAttributes } ) } /> );
		} );

		// Assert.
		expect( setAttributes ).toHaveBeenCalledWith(
			expect.objectContaining( {
				style: expect.objectContaining( {
					color: expect.objectContaining( {
						background: '#FFEE85',
						text: '#222222',
					} ),
				} ),
			} )
		);
	} );

	test( 'does not overwrite block color attributes already set', () => {
		// Arrange.
		const setLabel = jest.fn();
		const setBgColor = jest.fn();
		const setTextColor = jest.fn();
		const setAttributes = jest.fn();
		mockUseEntityProp
			.mockReturnValueOnce( [ 'Clearance', setLabel, undefined ] )
			.mockReturnValueOnce( [ '#FFEE85', setBgColor, undefined ] )
			.mockReturnValueOnce( [ '#222222', setTextColor, undefined ] );

		// Act.
		act( () => {
			render(
				<Edit
					{ ...makeProps( {
						attributes: {
							style: {
								color: {
									background: '#FF0000',
									text: '#0000FF',
								},
							},
						},
						setAttributes,
					} ) }
				/>
			);
		} );

		// Assert.
		expect( setAttributes ).not.toHaveBeenCalled();
	} );
} );
