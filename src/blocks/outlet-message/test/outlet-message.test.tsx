/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { render, screen, fireEvent } from '@testing-library/react';
import { Edit } from '../edit';

jest.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: jest.fn( ( props ) => props ?? {} ),
	RichText: jest.fn(
		( {
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
		)
	),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( str: string ) => str ),
} ) );

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

import { useEntityProp } from '@wordpress/core-data';
import { RichText } from '@wordpress/block-editor';

const mockUseEntityProp = useEntityProp as jest.Mock;
const mockRichText = RichText as jest.Mock;

describe( 'Edit', () => {
	test( 'renders empty message when setting is undefined', () => {
		// Arrange.
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			undefined,
			setMessage,
			undefined,
		] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByDisplayValue( '' ) ).toBeInTheDocument();
	} );

	test( 'renders empty message when setting is empty string', () => {
		// Arrange.
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [ '', setMessage, undefined ] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect( screen.getByDisplayValue( '' ) ).toBeInTheDocument();
	} );

	test( 'renders message with value from global setting', () => {
		// Arrange.
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			'Final sale — no returns.',
			setMessage,
			undefined,
		] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect(
			screen.getByDisplayValue( 'Final sale — no returns.' )
		).toBeInTheDocument();
	} );

	test( 'calls setMessage with new value when content changes', () => {
		// Arrange.
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			'Not eligible for change of mind returns',
			setMessage,
			undefined,
		] );
		render( <Edit /> );
		const input = screen.getByDisplayValue(
			'Not eligible for change of mind returns'
		);

		// Act.
		fireEvent.change( input, {
			target: { value: 'Final sale — no returns.' },
		} );

		// Assert.
		expect( setMessage ).toHaveBeenCalledWith( 'Final sale — no returns.' );
	} );

	test( 'disables rich text formatting controls', () => {
		// Arrange.
		mockRichText.mockClear();
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			'Final sale',
			setMessage,
			undefined,
		] );

		// Act.
		render( <Edit /> );

		// Assert.
		const [ richTextProps ] = mockRichText.mock.calls[ 0 ];
		expect( richTextProps.allowedFormats ).toEqual( [] );
	} );
} );
