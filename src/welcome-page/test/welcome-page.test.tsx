/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ClipboardEventHandler, ReactNode } from 'react';
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
		disabled,
		label,
		onChange,
		onPaste,
		value,
	}: {
		disabled?: boolean;
		label: string;
		onChange: ( value: string ) => void;
		onPaste: ClipboardEventHandler< HTMLInputElement >;
		value: string;
	} ) => (
		<input
			aria-label={ label }
			disabled={ disabled }
			onChange={ ( event ) => onChange( event.target.value ) }
			onPaste={ onPaste }
			value={ value }
		/>
	),
} ) );

const mockApiFetch = jest.mocked( apiFetch );
const mockFetch = jest.fn();
global.fetch = mockFetch;

const helpUrl = 'https://outletpro.zip/help/license-key';
const buyUrl = 'https://outletpro.zip/buy';
const licenseKey = '38B1460A-5104-4067-A91D-77B872934D51';

function arrangeGlobals( {
	hostname = 'example.com',
	isLocalEnvironment = false,
	prefilledLicenseKey = '',
} = {} ) {
	Object.assign( globalThis, {
		outletproWelcomePage: {
			hostname,
			isLocalEnvironment: isLocalEnvironment ? '1' : '',
			licenseKey: prefilledLicenseKey,
			productsUrl: '/wp-admin/edit.php?post_type=product',
		},
	} );
	mockApiFetch.mockReset();
	mockFetch.mockReset();
}

function validationResponse( {
	activationLimit = 5,
	activationUsage = 2,
	productId = 1279790,
	valid = true,
}: {
	activationLimit?: number | null;
	activationUsage?: number;
	productId?: number;
	valid?: boolean;
} = {} ) {
	return {
		json: () =>
			Promise.resolve( {
				valid,
				license_key: {
					activation_limit: activationLimit,
					activation_usage: activationUsage,
				},
				meta: { product_id: productId },
			} ),
	};
}

function enterLicenseKey( value = licenseKey ) {
	fireEvent.change( screen.getByLabelText( /^License key$/i ), {
		target: { value },
	} );
}

async function validateLicenseKey( value = licenseKey ) {
	await act( async () => enterLicenseKey( value ) );
}

function activateButton() {
	return screen.getByRole( 'button', { name: 'Activate site' } );
}

test( 'renders the default activation state and agreement', () => {
	// Arrange.
	arrangeGlobals();

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect( activateButton() ).toBeDisabled();
	expect(
		screen.getByText( /Need a premium license\?/i )
	).toBeInTheDocument();
	expect(
		screen.getByRole( 'link', { name: 'Purchase a license' } )
	).toHaveAttribute( 'href', buyUrl );
	expect(
		screen.getByRole( 'link', { name: 'find your license key' } )
	).toHaveAttribute( 'href', helpUrl );
	expect(
		screen.getByText( /By continuing, you agree/i )
	).toBeInTheDocument();
} );

test( 'automatically validates a 36-character prefilled key', async () => {
	// Arrange.
	arrangeGlobals( { prefilledLicenseKey: licenseKey } );
	mockFetch.mockResolvedValue( validationResponse() );

	// Act.
	await act( async () => render( <WelcomePage /> ) );

	// Assert.
	expect( screen.getByLabelText( /^License key$/i ) ).toHaveValue(
		licenseKey
	);
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
	expect( activateButton() ).toBeEnabled();
} );

test( 'does not automatically validate a shorter prefilled key', () => {
	// Arrange.
	arrangeGlobals( { prefilledLicenseKey: 'ABCD-1234' } );

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect( mockFetch ).not.toHaveBeenCalled();
} );

test( 'normalizes changes and validates only at exactly 36 characters', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( validationResponse() );
	render( <WelcomePage /> );

	// Act.
	enterLicenseKey( 'abcd-1234' );
	await validateLicenseKey( ` ${ licenseKey.toLowerCase() } ` );

	// Assert.
	expect( screen.getByLabelText( /^License key$/i ) ).toHaveValue(
		licenseKey
	);
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
	expect( mockFetch.mock.calls[ 0 ][ 1 ].body.toString() ).toBe(
		`license_key=${ licenseKey }`
	);
} );

test( 'validates pasted text regardless of length', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( validationResponse() );
	render( <WelcomePage /> );
	const input = screen.getByLabelText( /^License key$/i );

	// Act.
	await act( async () => {
		fireEvent.paste( input );
		fireEvent.change( input, { target: { value: 'abcd-1234' } } );
	} );

	// Assert.
	expect( input ).toHaveValue( 'ABCD-1234' );
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
} );

test( 'shows the validating state while the request is pending', () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockReturnValue( new Promise( () => undefined ) );
	render( <WelcomePage /> );

	// Act.
	enterLicenseKey();

	// Assert.
	expect( screen.getByText( 'Validating...' ) ).toBeInTheDocument();
	expect( activateButton() ).toBeDisabled();
} );

test( 'disables activation while revalidating a previously successful key', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValueOnce( validationResponse() );
	mockFetch.mockReturnValueOnce( new Promise( () => undefined ) );
	render( <WelcomePage /> );
	await validateLicenseKey();
	expect( activateButton() ).toBeEnabled();

	// Act.
	enterLicenseKey( '28B1460A-5104-4067-A91D-77B872934D51' );

	// Assert.
	expect( screen.getByText( 'Validating...' ) ).toBeInTheDocument();
	expect( activateButton() ).toBeDisabled();
} );

test.each( [
	{
		name: 'available',
		response: validationResponse(),
		message: '✅ 3 of 5 site activations available',
		role: 'status',
		enabled: true,
	},
	{
		name: 'unlimited',
		response: validationResponse( { activationLimit: null } ),
		message: '✅ Unlimited site activations available',
		role: 'status',
		enabled: true,
	},
	{
		name: 'exhausted',
		response: validationResponse( { activationUsage: 5 } ),
		message: '❌ License has reached the 5-site activation limit',
		role: 'status',
		enabled: false,
	},
	{
		name: 'server-invalid',
		response: validationResponse( { valid: false } ),
		message: 'Please check your premium license key and try again.',
		role: 'alert',
		enabled: false,
	},
	{
		name: 'wrong-product',
		response: validationResponse( { productId: 1234567 } ),
		message: 'Please check your premium license key and try again.',
		role: 'alert',
		enabled: false,
	},
] )( 'renders the $name validation result', async ( scenario ) => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( scenario.response );
	render( <WelcomePage /> );

	// Act.
	await validateLicenseKey();

	if ( ! scenario.enabled ) {
		fireEvent.click( activateButton() );
	}

	// Assert.
	expect( screen.getByRole( scenario.role ) ).toHaveTextContent(
		scenario.message
	);
	expect( activateButton() ).toHaveProperty( 'disabled', ! scenario.enabled );

	if ( ! scenario.enabled ) {
		expect( mockApiFetch ).not.toHaveBeenCalled();
	}

	if ( scenario.name === 'exhausted' ) {
		expect(
			screen.getByRole( 'link', { name: 'Learn more' } )
		).toHaveAttribute( 'href', helpUrl );
	}
} );

test.each( [
	[ 'network failure', () => mockFetch.mockRejectedValue( new Error() ) ],
	[
		'malformed response',
		() =>
			mockFetch.mockResolvedValue( {
				json: () =>
					Promise.resolve( {
						valid: true,
						meta: { product_id: 1279790 },
					} ),
			} ),
	],
] )( 'shows a service error for a %s', async ( name, mockRequest ) => {
	// Arrange.
	arrangeGlobals();
	mockRequest();
	render( <WelcomePage /> );

	// Act.
	await validateLicenseKey();

	// Assert.
	expect(
		screen.getByText(
			'Unable to contact the licensing service. Please try again.'
		)
	).toBeInTheDocument();
	expect( activateButton() ).toBeDisabled();
} );

test( 'gives a valid local site precedence over exhausted capacity and saves it', async () => {
	// Arrange.
	arrangeGlobals( { hostname: 'shop.local', isLocalEnvironment: true } );
	mockFetch.mockResolvedValue( validationResponse( { activationUsage: 5 } ) );
	mockApiFetch.mockResolvedValue( {} );
	render( <WelcomePage /> );

	// Act.
	await validateLicenseKey();

	// Assert.
	expect( screen.getByRole( 'status' ) ).toHaveTextContent(
		'shop.local License includes unlimited local sites.'
	);
	expect(
		screen.getByRole( 'link', { name: 'Learn more' } )
	).toHaveAttribute( 'href', helpUrl );
	expect( activateButton() ).toBeEnabled();

	// Act.
	await act( async () => fireEvent.click( activateButton() ) );

	// Assert.
	expect( mockApiFetch ).toHaveBeenCalledTimes( 1 );
	expect( screen.getByText( /Success!/i ) ).toBeInTheDocument();
} );

test( 'resets validation when a validated key is edited', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( validationResponse() );
	render( <WelcomePage /> );
	await validateLicenseKey();

	// Act.
	enterLicenseKey( 'ABCD' );

	// Assert.
	expect(
		screen.getByText( /Need a premium license\?/i )
	).toBeInTheDocument();
	expect( activateButton() ).toBeDisabled();
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
	mockFetch.mockResolvedValueOnce( validationResponse( { valid: false } ) );
	render( <WelcomePage /> );

	// Act.
	enterLicenseKey();
	await validateLicenseKey( '28B1460A-5104-4067-A91D-77B872934D51' );
	await act( async () => resolveFirstRequest( validationResponse() ) );

	// Assert.
	expect(
		screen.getByText(
			'Please check your premium license key and try again.'
		)
	).toBeInTheDocument();
} );

test( 'saves an eligible key without revalidating and preserves the success page', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( validationResponse() );
	mockApiFetch.mockResolvedValue( {} );
	render( <WelcomePage /> );
	await validateLicenseKey();

	// Act.
	await act( async () => fireEvent.click( activateButton() ) );

	// Assert.
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
	expect( mockApiFetch ).toHaveBeenCalledWith( {
		path: '/wp/v2/settings',
		method: 'POST',
		data: { outletpro_license_key: licenseKey },
	} );
	expect( screen.getByText( /Success!/i ) ).toBeInTheDocument();
	expect(
		screen.getByRole( 'link', { name: /Get Started/i } )
	).toHaveAttribute( 'href', '/wp-admin/edit.php?post_type=product' );
} );

test( 'surfaces a save failure and keeps the validated key eligible', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( validationResponse() );
	mockApiFetch.mockRejectedValue( new Error() );
	render( <WelcomePage /> );
	await validateLicenseKey();

	// Act.
	await act( async () => fireEvent.click( activateButton() ) );

	// Assert.
	expect(
		screen.getByText( 'Unable to apply the license. Please try again.' )
	).toBeInTheDocument();
	expect( activateButton() ).toBeEnabled();
} );
