/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode } from 'react';
import { render, screen, act, fireEvent } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { WelcomePage } from '../WelcomePage';

jest.mock( '@wordpress/api-fetch', () => ( {
	__esModule: true,
	default: jest.fn(),
} ) );

jest.mock( '@wordpress/components', () => ( {
	Button: ( {
		children,
		href,
		onClick,
	}: {
		children: ReactNode;
		href?: string;
		onClick?: () => void;
	} ) =>
		href ? (
			<a href={ href }>{ children }</a>
		) : (
			<button type="button" onClick={ onClick }>
				{ children }
			</button>
		),

	TextControl: ( {
		label,
		value,
		onChange,
	}: {
		label: string;
		value: string;
		onChange: ( value: string ) => void;
	} ) => (
		<input
			aria-label={ label }
			value={ value }
			onChange={ ( event ) => onChange( event.target.value ) }
		/>
	),
} ) );

const mockApiFetch = apiFetch as unknown as jest.Mock;
const mockFetch = jest.fn();
global.fetch = mockFetch;

function arrangeGlobals( {
	licenseKey = '',
	productsUrl = '/wp-admin/edit.php?post_type=product',
}: { licenseKey?: string; productsUrl?: string } = {} ) {
	( window as any ).outletproWelcomePage = { licenseKey, productsUrl };
	mockApiFetch.mockReset();
	mockFetch.mockReset();
}

test( 'renders the welcome message', () => {
	// Arrange.
	arrangeGlobals();

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByText( /Thank you for installing Outlet Pro/i )
	).toBeInTheDocument();
} );

test( 'renders the license key input', () => {
	// Arrange.
	arrangeGlobals();

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByLabelText( /Premium license key/i )
	).toBeInTheDocument();
} );

test( 'renders the Continue button', () => {
	// Arrange.
	arrangeGlobals();

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Continue/i } )
	).toBeInTheDocument();
} );

test( 'pre-fills license key from outletproWelcomePage global', () => {
	// Arrange.
	arrangeGlobals( { licenseKey: 'ABCD-1234' } );

	// Act.
	render( <WelcomePage /> );

	// Assert.
	const input = screen.getByLabelText(
		/Premium license key/i
	) as HTMLInputElement;
	expect( input.value ).toBe( 'ABCD-1234' );
} );

test( 'shows error message when server responds with valid: false', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( {
		json: () =>
			Promise.resolve( {
				valid: false,
				meta: { product_id: 1279790 },
			} ),
	} );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );
	} );

	// Assert.
	expect( screen.getByText( /Invalid license key/i ) ).toBeInTheDocument();
} );

test( 'shows error message when product ID is not allowed', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( {
		json: () =>
			Promise.resolve( {
				valid: true,
				meta: { product_id: 1234567 },
			} ),
	} );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );
	} );

	// Assert.
	expect( screen.getByText( /Invalid license key/i ) ).toBeInTheDocument();
	expect( mockApiFetch ).not.toHaveBeenCalled();
} );

test( 'shows error message when server fetch throws', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockRejectedValue( new Error( 'Network error' ) );

	// Act.
	render( <WelcomePage /> );
	fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );

	// Assert.
	expect( await screen.findByRole( 'alert' ) ).toBeInTheDocument();
} );

test( 'shows success message after valid license key is accepted and saved', async () => {
	// Arrange.
	arrangeGlobals( { licenseKey: 'ABCD-1234' } );
	mockFetch.mockResolvedValue( {
		json: () =>
			Promise.resolve( {
				valid: true,
				meta: { product_id: 1279790 },
			} ),
	} );
	mockApiFetch.mockResolvedValue( {} );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );
	} );

	// Assert.
	expect( screen.getByText( /Success!/i ) ).toBeInTheDocument();
	expect( mockFetch ).toHaveBeenCalledWith(
		'https://api.lemonsqueezy.com/v1/licenses/validate',
		expect.objectContaining( {
			method: 'POST',
			headers: {
				Accept: 'application/json',
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: expect.any( URLSearchParams ),
		} )
	);
	const request = mockFetch.mock.calls[ 0 ][ 1 ];
	expect( request.body.toString() ).toBe( 'license_key=ABCD-1234' );
} );

test( 'shows error when REST API save fails after valid server response', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( {
		json: () =>
			Promise.resolve( {
				valid: true,
				meta: { product_id: 1279790 },
			} ),
	} );
	mockApiFetch.mockRejectedValue( new Error( 'Forbidden' ) );

	// Act.
	render( <WelcomePage /> );
	fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );

	// Assert.
	expect( await screen.findByRole( 'alert' ) ).toBeInTheDocument();
} );

test( 'success view shows Products link', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( {
		json: () =>
			Promise.resolve( {
				valid: true,
				meta: { product_id: 1279790 },
			} ),
	} );
	mockApiFetch.mockResolvedValue( {} );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.click( screen.getByRole( 'button', { name: /Continue/i } ) );
	} );

	// Assert.
	const productsLink = screen.getByRole( 'link', { name: /Get Started/i } );
	expect( productsLink ).toHaveAttribute(
		'href',
		'/wp-admin/edit.php?post_type=product'
	);
} );

test( 'normalizes the license key', () => {
	// Arrange.
	arrangeGlobals();
	render( <WelcomePage /> );

	const input = screen.getByLabelText(
		/Premium license key/i
	) as HTMLInputElement;

	// Act.
	fireEvent.change( input, {
		target: { value: 'abcd-1234' },
	} );

	// Assert.
	expect( input ).toHaveValue( 'ABCD-1234' );
} );

test( 'trims the license key', () => {
	// Arrange.
	arrangeGlobals();
	render( <WelcomePage /> );

	const input = screen.getByLabelText(
		/Premium license key/i
	) as HTMLInputElement;

	// Act.
	fireEvent.change( input, {
		target: { value: '  ABCD-1234  ' },
	} );

	// Assert.
	expect( input ).toHaveValue( 'ABCD-1234' );
} );
