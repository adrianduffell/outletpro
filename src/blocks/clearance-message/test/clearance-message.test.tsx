import { render, screen, fireEvent } from '@testing-library/react';
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

const defaultAttributes = {
	message: 'Choose carefully! Clearance products are ineligible for returns',
};

describe( 'Edit', () => {
	test( 'renders message with default text', () => {
		// Arrange.
		const setAttributes = jest.fn();

		// Act.
		render(
			<Edit
				attributes={ defaultAttributes }
				setAttributes={ setAttributes }
			/>
		);

		// Assert.
		expect(
			screen.getByDisplayValue(
				'Choose carefully! Clearance products are ineligible for returns'
			)
		).toBeInTheDocument();
	} );

	test( 'renders message with custom message attribute', () => {
		// Arrange.
		const setAttributes = jest.fn();

		// Act.
		render(
			<Edit
				attributes={ { message: 'No returns on clearance!' } }
				setAttributes={ setAttributes }
			/>
		);

		// Assert.
		expect(
			screen.getByDisplayValue( 'No returns on clearance!' )
		).toBeInTheDocument();
	} );

	test( 'calls setAttributes with new message when content changes', () => {
		// Arrange.
		const setAttributes = jest.fn();
		render(
			<Edit
				attributes={ defaultAttributes }
				setAttributes={ setAttributes }
			/>
		);
		const input = screen.getByDisplayValue(
			'Choose carefully! Clearance products are ineligible for returns'
		);

		// Act.
		fireEvent.change( input, {
			target: { value: 'Final sale — no returns.' },
		} );

		// Assert.
		expect( setAttributes ).toHaveBeenCalledWith( {
			message: 'Final sale — no returns.',
		} );
	} );
} );
