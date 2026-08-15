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

jest.mock( '@wordpress/ui', () => ( { Link: 'a' } ) );

jest.mock( '@wordpress/components', () => ( {
	Button: ( {
		children,
		disabled,
		href,
		onClick,
	}: {
		children: ReactNode;
		disabled?: boolean;
		href?: string;
		onClick?: () => void;
	} ) =>
		href ? (
			<a href={ href }>{ children }</a>
		) : (
			<button type="button" disabled={ disabled } onClick={ onClick }>
				{ children }
			</button>
		),

	TextControl: ( {
		label,
		value,
		onChange,
		onPaste,
	}: {
		label: string;
		value: string;
		onChange: ( value: string ) => void;
		onPaste: () => void;
	} ) => (
		<input
			aria-label={ label }
			value={ value }
			onChange={ ( event ) => onChange( event.target.value ) }
			onPaste={ onPaste }
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

test( 'renders the Activate site button disabled', () => {
	// Arrange.
	arrangeGlobals();

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeDisabled();
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

test( 'does not validate a short prefilled license key', () => {
	// Arrange.
	arrangeGlobals( { licenseKey: 'ABCD-1234' } );

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect( mockFetch ).not.toHaveBeenCalled();
} );

test( 'validates a 36-character prefilled license key', async () => {
	// Arrange.
	arrangeGlobals( {
		licenseKey: '38B1460A-5104-4067-A91D-77B872934D51',
	} );
	mockFetch.mockReturnValue( new Promise( () => undefined ) );

	// Act.
	await act( async () => render( <WelcomePage /> ) );

	// Assert.
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeDisabled();
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
		fireEvent.change( screen.getByLabelText( /Premium license key/i ), {
			target: { value: '38B1460A-5104-4067-A91D-77B872934D51' },
		} );
	} );

	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeDisabled();
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
		fireEvent.change( screen.getByLabelText( /Premium license key/i ), {
			target: { value: '38B1460A-5104-4067-A91D-77B872934D51' },
		} );
	} );

	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeDisabled();
} );

test( 'shows error message when server fetch throws', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockRejectedValue( new Error( 'Network error' ) );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.change( screen.getByLabelText( /Premium license key/i ), {
			target: { value: '38B1460A-5104-4067-A91D-77B872934D51' },
		} );
	} );

	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeDisabled();
} );

test( 'shows success message after valid license key is accepted and saved', async () => {
	// Arrange.
	arrangeGlobals( { licenseKey: 'ABCD-1234' } );
	mockFetch.mockResolvedValue( {
		json: () =>
			Promise.resolve( {
				valid: true,
				license_key: {
					activation_limit: 5,
					activation_usage: 2,
				},
				meta: { product_id: 1279790 },
			} ),
	} );
	mockApiFetch.mockResolvedValue( {} );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.change( screen.getByLabelText( /Premium license key/i ), {
			target: { value: '38B1460A-5104-4067-A91D-77B872934D51' },
		} );
	} );
	await act( async () => {
		fireEvent.click(
			screen.getByRole( 'button', { name: /Activate site/i } )
		);
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
	expect( request.body.toString() ).toBe(
		'license_key=38B1460A-5104-4067-A91D-77B872934D51'
	);
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
} );

test( 'shows error when REST API save fails after valid server response', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( {
		json: () =>
			Promise.resolve( {
				valid: true,
				license_key: {
					activation_limit: 5,
					activation_usage: 2,
				},
				meta: { product_id: 1279790 },
			} ),
	} );
	mockApiFetch.mockRejectedValue( new Error( 'Forbidden' ) );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.change( screen.getByLabelText( /Premium license key/i ), {
			target: { value: '38B1460A-5104-4067-A91D-77B872934D51' },
		} );
	} );
	await act( async () => {
		fireEvent.click(
			screen.getByRole( 'button', { name: /Activate site/i } )
		);
	} );

	// Assert.
	expect( screen.getByText( /Unable to apply/i ) ).toBeInTheDocument();
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeEnabled();
} );

test( 'success view shows Products link', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( {
		json: () =>
			Promise.resolve( {
				valid: true,
				license_key: {
					activation_limit: 5,
					activation_usage: 2,
				},
				meta: { product_id: 1279790 },
			} ),
	} );
	mockApiFetch.mockResolvedValue( {} );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.change( screen.getByLabelText( /Premium license key/i ), {
			target: { value: '38B1460A-5104-4067-A91D-77B872934D51' },
		} );
	} );
	await act( async () => {
		fireEvent.click(
			screen.getByRole( 'button', { name: /Activate site/i } )
		);
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

const LICENSE_AVAILABLE = {
	valid: true,
	license_key: {
		activation_limit: 5,
		activation_usage: 2,
	},
	meta: { product_id: 1279790 },
};
const LICENSE_UNLIMITED = {
	valid: true,
	license_key: {
		activation_limit: null,
		activation_usage: 2,
	},
	meta: { product_id: 1279790 },
};
const LICENSE_EXHAUSTED = {
	valid: true,
	license_key: {
		activation_limit: 5,
		activation_usage: 5,
	},
	meta: { product_id: 1279790 },
};
const LICENSE_INVALID = { valid: false };

test( 'validates a pasted license key regardless of length', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( LICENSE_AVAILABLE ),
	} );
	render( <WelcomePage /> );
	const input = screen.getByLabelText( /Premium license key/i );
	// Act.
	fireEvent.paste( input );
	await act( async () => {
		fireEvent.change( input, { target: { value: 'ABCD-1234' } } );
	} );
	// Assert.
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
} );

test( 'disables activation while revalidating a previously valid key', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValueOnce( {
		json: () => Promise.resolve( LICENSE_AVAILABLE ),
	} );
	render( <WelcomePage /> );
	const input = screen.getByLabelText( /Premium license key/i );
	await act( async () => {
		fireEvent.change( input, {
			target: { value: '38B1460A-5104-4067-A91D-77B872934D51' },
		} );
	} );
	mockFetch.mockReturnValueOnce( new Promise( () => undefined ) );
	// Act.
	await act( async () => {
		fireEvent.change( input, {
			target: { value: '28B1460A-5104-4067-A91D-77B872934D51' },
		} );
	} );
	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeDisabled();
	// Act.
	await act( async () => {
		fireEvent.change( input, { target: { value: 'ABCD' } } );
	} );
	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeDisabled();
	expect( mockFetch ).toHaveBeenCalledTimes( 2 );
} );

test( 'enables activation for an unlimited license', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( LICENSE_UNLIMITED ),
	} );
	render( <WelcomePage /> );
	// Act.
	await act( async () => {
		fireEvent.change( screen.getByLabelText( /Premium license key/i ), {
			target: { value: '38B1460A-5104-4067-A91D-77B872934D51' },
		} );
	} );
	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeEnabled();
} );

test( 'disables activation for an exhausted license', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( LICENSE_EXHAUSTED ),
	} );
	render( <WelcomePage /> );
	// Act.
	await act( async () => {
		fireEvent.change( screen.getByLabelText( /Premium license key/i ), {
			target: { value: '38B1460A-5104-4067-A91D-77B872934D51' },
		} );
	} );
	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeDisabled();
} );

test( 'ignores a stale validation response', async () => {
	// Arrange.
	arrangeGlobals();
	let resolveFirstRequest: ( value: unknown ) => void = () => undefined;
	mockFetch.mockReturnValueOnce(
		new Promise( ( resolve ) => {
			resolveFirstRequest = resolve;
		} )
	);
	mockFetch.mockResolvedValueOnce( {
		json: () => Promise.resolve( LICENSE_INVALID ),
	} );
	render( <WelcomePage /> );
	const input = screen.getByLabelText( /Premium license key/i );
	// Act.
	fireEvent.change( input, {
		target: { value: '38B1460A-5104-4067-A91D-77B872934D51' },
	} );
	await act( async () => {
		fireEvent.change( input, {
			target: { value: '28B1460A-5104-4067-A91D-77B872934D51' },
		} );
	} );
	await act( async () =>
		resolveFirstRequest( {
			json: () => Promise.resolve( LICENSE_AVAILABLE ),
		} )
	);
	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeDisabled();
} );
