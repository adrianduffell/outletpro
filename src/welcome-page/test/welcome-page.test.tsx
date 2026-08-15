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

const mockApiFetch = jest.mocked( apiFetch );
const mockFetch = jest.fn();
global.fetch = mockFetch;

const licenseKey = '38B1460A-5104-4067-A91D-77B872934D51';
const productsUrl = '/wp-admin/edit.php?post_type=product';

function arrangeGlobals( {
	hostname = 'example.com',
	isLocalHost = false,
	licenseKey: prefilledLicenseKey = '',
} = {} ) {
	Object.assign( globalThis, {
		outletproWelcomePage: {
			hostname,
			isLocalHost: isLocalHost ? '1' : '',
			licenseKey: prefilledLicenseKey,
			productsUrl,
		},
	} );
	mockApiFetch.mockReset();
	mockFetch.mockReset();
}

function validationResponse(
	limit: number | null = 5,
	usage = 2,
	productId = 1279790,
	valid = true
) {
	return {
		json: async () => ( {
			valid,
			license_key: { activation_limit: limit, activation_usage: usage },
			meta: { product_id: productId },
		} ),
	};
}
const malformedResponse = {
	json: async () => ( { valid: true, meta: { product_id: 1279790 } } ),
};
const invalidResponse = validationResponse( 5, 2, 1279790, false );

const licenseKeyInput = () => screen.getByLabelText( /Premium license key/i );
const activateButton = () =>
	screen.getByRole( 'button', { name: /Activate site/i } );
const enterLicenseKey = async ( value = licenseKey ) =>
	act( async () =>
		fireEvent.change( licenseKeyInput(), { target: { value } } )
	);

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
	expect( activateButton() ).toBeDisabled();
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
	expect( mockFetch ).not.toHaveBeenCalled();
} );

test( 'validates a 36-character prefilled license key', async () => {
	// Arrange.
	arrangeGlobals( { licenseKey } );
	mockFetch.mockReturnValue( new Promise( () => undefined ) );

	// Act.
	await act( async () => render( <WelcomePage /> ) );

	// Assert.
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
	expect( activateButton() ).toBeDisabled();
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
	await enterLicenseKey();

	// Assert.
	expect( activateButton() ).toBeDisabled();
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
	await enterLicenseKey();

	// Assert.
	expect( activateButton() ).toBeDisabled();
} );

test( 'shows error message when server fetch throws', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockRejectedValue( new Error( 'Network error' ) );

	// Act.
	render( <WelcomePage /> );
	await enterLicenseKey();

	// Assert.
	expect( activateButton() ).toBeDisabled();
} );

test( 'shows success message after valid license key is accepted and saved', async () => {
	// Arrange.
	arrangeGlobals( { licenseKey } );
	mockFetch.mockResolvedValue( validationResponse() );
	mockApiFetch.mockResolvedValue( {} );

	// Act.
	await act( async () => render( <WelcomePage /> ) );
	await act( async () => fireEvent.click( activateButton() ) );

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
	expect( request.body.toString() ).toBe( `license_key=${ licenseKey }` );
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
} );

test( 'shows error when REST API save fails after valid server response', async () => {
	// Arrange.
	arrangeGlobals( { licenseKey } );
	mockFetch.mockResolvedValue( validationResponse() );
	mockApiFetch.mockRejectedValue( new Error( 'Forbidden' ) );

	// Act.
	await act( async () => render( <WelcomePage /> ) );
	await act( async () => fireEvent.click( activateButton() ) );

	// Assert.
	expect( screen.getByText( /Unable to apply/i ) ).toBeInTheDocument();
	expect( activateButton() ).toBeEnabled();
} );

test( 'success view shows Products link', async () => {
	// Arrange.
	arrangeGlobals( { licenseKey } );
	mockFetch.mockResolvedValue( validationResponse() );
	mockApiFetch.mockResolvedValue( {} );

	// Act.
	await act( async () => render( <WelcomePage /> ) );
	await act( async () => fireEvent.click( activateButton() ) );

	// Assert.
	const productsLink = screen.getByRole( 'link', { name: /Get Started/i } );
	expect( productsLink ).toHaveAttribute(
		'href',
		'/wp-admin/edit.php?post_type=product'
	);
} );

test( 'normalizes the license key', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( validationResponse() );
	render( <WelcomePage /> );

	const input = screen.getByLabelText(
		/Premium license key/i
	) as HTMLInputElement;

	// Act.
	await enterLicenseKey( licenseKey.toLowerCase() );

	// Assert.
	expect( input ).toHaveValue( licenseKey );
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
} );

test( 'trims the license key', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( validationResponse() );
	render( <WelcomePage /> );

	const input = screen.getByLabelText(
		/Premium license key/i
	) as HTMLInputElement;

	// Act.
	fireEvent.paste( input );
	await enterLicenseKey( '  ABCD-1234  ' );

	// Assert.
	expect( input ).toHaveValue( 'ABCD-1234' );
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
} );

test( 'disables activation while revalidating a previously valid key', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValueOnce( validationResponse() );
	render( <WelcomePage /> );
	await enterLicenseKey();
	mockFetch.mockReturnValueOnce( new Promise( () => undefined ) );
	// Act.
	await enterLicenseKey( licenseKey.replace( '3', '2' ) );
	// Assert.
	expect( activateButton() ).toBeDisabled();
	// Act.
	await enterLicenseKey( 'ABCD' );
	// Assert.
	expect( activateButton() ).toBeDisabled();
	expect( mockFetch ).toHaveBeenCalledTimes( 2 );
} );

test.each( [
	[ false, validationResponse( null ), false ],
	[ false, validationResponse( 5, 5 ), true ],
	[ true, validationResponse( 5, 5 ), false ],
	[ true, malformedResponse, true ],
] )(
	'sets activation eligibility for validation result %#',
	async ( isLocalHost, response, disabled ) => {
		// Arrange.
		arrangeGlobals( { isLocalHost } );
		mockFetch.mockResolvedValue( response );
		render( <WelcomePage /> );
		// Act.
		await enterLicenseKey();
		// Assert.
		expect( activateButton() ).toHaveProperty( 'disabled', disabled );
	}
);

test( 'ignores a stale validation response', async () => {
	// Arrange.
	arrangeGlobals();
	let resolveFirstRequest: ( value: unknown ) => void = () => undefined;
	mockFetch.mockReturnValueOnce(
		new Promise( ( resolve ) => {
			resolveFirstRequest = resolve;
		} )
	);
	mockFetch.mockResolvedValueOnce( invalidResponse );
	render( <WelcomePage /> );
	// Act.
	fireEvent.change( licenseKeyInput(), { target: { value: licenseKey } } );
	await enterLicenseKey( licenseKey.replace( '3', '2' ) );
	await act( async () => resolveFirstRequest( validationResponse() ) );
	// Assert.
	expect( activateButton() ).toBeDisabled();
} );
