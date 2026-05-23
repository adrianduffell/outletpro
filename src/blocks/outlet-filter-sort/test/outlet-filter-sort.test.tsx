import { render, screen } from '@testing-library/react';
import { Edit } from '../edit';

jest.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: jest.fn( () => ( {} ) ),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( str: string ) => str ),
} ) );

describe( 'Edit', () => {
	test( 'renders disabled default sorting select in editor', () => {
		// Arrange.

		// Act.
		render( <Edit /> );

		// Assert.
		const select = screen.getByRole( 'combobox' );
		const option = screen.getByRole( 'option', {
			name: 'Default sorting',
		} ) as HTMLOptionElement;
		expect( select ).toBeDisabled();
		expect( option.selected ).toBe( true );
	} );
} );
