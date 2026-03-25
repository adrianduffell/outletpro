import { render, screen } from '@testing-library/react';
import { Sample } from '../index';

jest.mock( '@wordpress/escape-html', () => ( {
	escapeHTML: ( value ) => value,
} ) );

test( 'renders sample component', () => {
	// Act.
	render( <Sample /> );

	// Assert.
	expect( screen.getByText( 'WC Clearance' ) ).toBeInTheDocument();
} );

test( 'renders with the correct CSS class', () => {
	// Act.
	const { container } = render( <Sample /> );

	// Assert.
	expect( container.firstChild ).toHaveClass( 'wc-clearance-sample' );
} );
