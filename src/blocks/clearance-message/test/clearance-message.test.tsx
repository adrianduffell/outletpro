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

jest.mock( '@wordpress/core-data', () => ( {
	useEntityProp: jest.fn(),
} ) );

jest.mock( '@wordpress/data', () => ( {
	useSelect: jest.fn(
		( selector: ( select: ( store: string ) => unknown ) => unknown ) =>
			selector( ( store: string ) => {
				if ( store === 'core/block-editor' ) {
					return {
						getSettings: () => ( {
							wcClearanceDefaultMessage: 'Only while stocks last',
						} ),
					};
				}
				return {};
			} )
	),
} ) );

import { useEntityProp } from '@wordpress/core-data';

const mockUseEntityProp = useEntityProp as jest.Mock;

describe( 'Edit', () => {
	test( 'renders message with default text when setting is undefined', () => {
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
		expect(
			screen.getByDisplayValue( 'Only while stocks last' )
		).toBeInTheDocument();
	} );

	test( 'renders message with supplies last when store is in US', () => {
		// Arrange.
		const { useSelect } = jest.requireMock( '@wordpress/data' );
		( useSelect as jest.Mock ).mockImplementationOnce(
			( selector: ( select: ( store: string ) => unknown ) => unknown ) =>
				selector( ( store: string ) => {
					if ( store === 'core/block-editor' ) {
						return {
							getSettings: () => ( {
								wcClearanceDefaultMessage:
									'Only while supplies last',
							} ),
						};
					}
					return {};
				} )
		);
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [
			undefined,
			setMessage,
			undefined,
		] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect(
			screen.getByDisplayValue( 'Only while supplies last' )
		).toBeInTheDocument();
	} );

	test( 'renders message with default text when setting is empty string', () => {
		// Arrange.
		const setMessage = jest.fn();
		mockUseEntityProp.mockReturnValue( [ '', setMessage, undefined ] );

		// Act.
		render( <Edit /> );

		// Assert.
		expect(
			screen.getByDisplayValue( 'Only while stocks last' )
		).toBeInTheDocument();
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
			'Only while stocks last',
			setMessage,
			undefined,
		] );
		render( <Edit /> );
		const input = screen.getByDisplayValue( 'Only while stocks last' );

		// Act.
		fireEvent.change( input, {
			target: { value: 'Final sale — no returns.' },
		} );

		// Assert.
		expect( setMessage ).toHaveBeenCalledWith( 'Final sale — no returns.' );
	} );
} );
