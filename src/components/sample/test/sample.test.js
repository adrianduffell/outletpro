import { render, screen } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { Sample } from '../index';

jest.mock( '@wordpress/api-fetch', () => ( {
	__esModule: true,
	default: jest.fn(),
} ) );

test( 'renders label from WooCommerce API', async () => {
	// Arrange.
	apiFetch.mockResolvedValue( [ { name: 'WC Clearance' } ] );

	// Act.
	render( <Sample /> );

	// Assert.
	expect( await screen.findByText( 'WC Clearance' ) ).toBeInTheDocument();
	expect( apiFetch ).toHaveBeenCalledWith( {
		path: '/wc/v3/products?per_page=1',
	} );
} );
