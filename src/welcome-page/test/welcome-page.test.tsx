import { render, screen, act, fireEvent } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { WelcomePage } from '../WelcomePage';

jest.mock( '@wordpress/api-fetch', () => ( {
	__esModule: true,
	default: jest.fn(),
} ) );

const mockApiFetch = apiFetch as unknown as jest.Mock;

const mockFetch = jest.fn();
global.fetch = mockFetch;

beforeEach( () => {
	( window as any ).outletproWelcomePage = {
		licenseKey: '',
		productsUrl: '/wp-admin/edit.php?post_type=product',
	};
	mockApiFetch.mockClear();
	mockFetch.mockClear();
} );

test( 'renders the welcome message', () => {
	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByText( /Thank you for installing Outlet Pro/i )
	).toBeInTheDocument();
} );

test( 'renders the license key input', () => {
	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByLabelText( /Premium license key/i )
	).toBeInTheDocument();
} );

test( 'renders the Continue button', () => {
	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Continue/i } )
	).toBeInTheDocument();
} );

test( 'pre-fills license key from outletproWelcomePage global', () => {
	// Arrange.
	( window as any ).outletproWelcomePage.licenseKey = 'ABCD-1234';

	// Act.
	render( <WelcomePage /> );

	// Assert.
	const input = screen.getByLabelText(
		/Premium license key/i
	) as HTMLInputElement;
	expect( input.value ).toBe( 'ABCD-1234' );
} );

test( 'shows error message when server responds with success: false', async () => {
	// Arrange.
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( { success: false } ),
	} );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );
	} );

	// Assert.
	expect( screen.getByText( /Invalid license key/i ) ).toBeInTheDocument();
} );

test( 'shows error message when server fetch throws', async () => {
	// Arrange.
	mockFetch.mockRejectedValue( new Error( 'Network error' ) );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );
	} );

	// Assert.
	expect( screen.getByText( /Invalid license key/i ) ).toBeInTheDocument();
} );

test( 'shows success message after valid license key is accepted and saved', async () => {
	// Arrange.
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( { success: true } ),
	} );
	mockApiFetch.mockResolvedValue( {} );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );
	} );

	// Assert.
	expect(
		screen.getByText( /🎉 Success! Outlet Pro is now set up\./i )
	).toBeInTheDocument();
} );

test( 'saves license key via WP REST API when validation succeeds', async () => {
	// Arrange.
	( window as any ).outletproWelcomePage.licenseKey = 'MY-KEY';
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( { success: true } ),
	} );
	mockApiFetch.mockResolvedValue( {} );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );
	} );

	// Assert.
	expect( mockApiFetch ).toHaveBeenCalledWith(
		expect.objectContaining( {
			path: '/wp/v2/settings',
			method: 'POST',
			data: { outletpro_license_key: 'MY-KEY' },
		} )
	);
} );

test( 'shows error when REST API save fails after valid server response', async () => {
	// Arrange.
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( { success: true } ),
	} );
	mockApiFetch.mockRejectedValue( new Error( 'Forbidden' ) );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );
	} );

	// Assert.
	expect( screen.getByText( /Invalid license key/i ) ).toBeInTheDocument();
} );

test( 'success view shows Products link', async () => {
	// Arrange.
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( { success: true } ),
	} );
	mockApiFetch.mockResolvedValue( {} );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );
	} );

	// Assert.
	const productsLink = screen.getByRole( 'link', { name: /Products/i } );
	expect( productsLink ).toHaveAttribute(
		'href',
		'/wp-admin/edit.php?post_type=product'
	);
} );
