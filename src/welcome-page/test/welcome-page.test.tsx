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
jest.mock( '@wordpress/ui', () => ( {
	Link: ( { children, href }: { children?: ReactNode; href: string } ) => (
		<a href={ href }>{ children }</a>
	),
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
const licenseKey = '38B1460A-5104-4067-A91D-77B872934D51';
const productsUrl = '/wp-admin/edit.php?post_type=product';
function arrangeGlobals( {
	hostname = 'example.com',
	isLocalEnvironment = false,
	licenseKey: prefilledLicenseKey = '',
} = {} ) {
	Object.assign( globalThis, {
		outletproWelcomePage: {
			hostname,
			isLocalEnvironment: isLocalEnvironment ? '1' : '',
			licenseKey: prefilledLicenseKey,
			productsUrl,
		},
	} );
	mockApiFetch.mockReset();
	mockFetch.mockReset();
}
function validationResponse(
	activationLimit: number | null = 5,
	activationUsage = 2,
	productId = 1279790,
	valid = true
) {
	return {
		json: async () => ( {
			valid,
			license_key: {
				activation_limit: activationLimit,
				activation_usage: activationUsage,
			},
			meta: { product_id: productId },
		} ),
	};
}
const licenseKeyInput = () => screen.getByLabelText( /Premium license key/i );
const activateButton = () =>
	screen.getByRole( 'button', { name: 'Activate site' } );
const enterLicenseKey = async ( value = licenseKey ) =>
	act( async () =>
		fireEvent.change( licenseKeyInput(), { target: { value } } )
	);
test( 'renders the welcome activation state and agreement', () => {
	// Arrange.
	arrangeGlobals();
	// Act.
	render( <WelcomePage /> );
	// Assert.
	expect( licenseKeyInput() ).toBeInTheDocument();
	expect( activateButton() ).toBeDisabled();
	expect(
		screen.getByText( /By continuing, you agree/i )
	).toBeInTheDocument();
} );
test.each( [
	[ licenseKey, true ],
	[ 'ABCD-1234', false ],
] )(
	'handles automatic validation for a prefilled key',
	async ( value, validates ) => {
		// Arrange.
		arrangeGlobals( { licenseKey: value } );
		mockFetch.mockResolvedValue( validationResponse() );
		// Act.
		await act( async () => render( <WelcomePage /> ) );
		// Assert.
		expect( licenseKeyInput() ).toHaveValue( value );
		expect( mockFetch ).toHaveBeenCalledTimes( validates ? 1 : 0 );
		expect( activateButton() ).toHaveProperty( 'disabled', ! validates );
	}
);
test( 'normalizes changes and validates only at exactly 36 characters', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( validationResponse() );
	render( <WelcomePage /> );
	// Act.
	await enterLicenseKey( 'abcd-1234' );
	await enterLicenseKey( ` ${ licenseKey.toLowerCase() } ` );
	// Assert.
	expect( licenseKeyInput() ).toHaveValue( licenseKey );
	expect( mockFetch ).toHaveBeenCalledTimes( 1 );
	expect( mockFetch.mock.calls[ 0 ][ 1 ].body.toString() ).toBe(
		`license_key=${ licenseKey }`
	);
	// Act.
	await enterLicenseKey( 'ABCD' );
	// Assert.
	expect( activateButton() ).toBeDisabled();
	// Act.
	await act( async () => {
		fireEvent.paste( licenseKeyInput() );
		fireEvent.change( licenseKeyInput(), {
			target: { value: 'abcd-1234' },
		} );
	} );
	// Assert.
	expect( licenseKeyInput() ).toHaveValue( 'ABCD-1234' );
	expect( mockFetch ).toHaveBeenCalledTimes( 2 );
} );
test.each( [ false, true ] )(
	'disables activation while validating (previously valid: %s)',
	async ( previouslyValid ) => {
		// Arrange.
		arrangeGlobals();
		render( <WelcomePage /> );
		if ( previouslyValid ) {
			mockFetch.mockResolvedValueOnce( validationResponse() );
			await enterLicenseKey();
		}
		mockFetch.mockReturnValueOnce( new Promise( () => undefined ) );
		// Act.
		fireEvent.change( licenseKeyInput(), {
			target: {
				value: previouslyValid
					? licenseKey.replace( '3', '2' )
					: licenseKey,
			},
		} );
		// Assert.
		expect( screen.getByText( 'Validating...' ) ).toBeInTheDocument();
		expect( activateButton() ).toBeDisabled();
	}
);
const invalidResponse = validationResponse( 5, 2, 1279790, false );
test.each( [
	[ 'available', validationResponse(), true ],
	[ 'unlimited', validationResponse( null ), true ],
	[ 'exhausted', validationResponse( 5, 5 ), false ],
	[ 'server-invalid', invalidResponse, false ],
	[ 'wrong-product', validationResponse( 5, 2, 1 ), false ],
] )( 'handles the %s validation result', async ( name, response, enabled ) => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( response );
	render( <WelcomePage /> );
	// Act.
	await enterLicenseKey();
	if ( ! enabled ) {
		fireEvent.click( activateButton() );
	}
	// Assert.
	expect( activateButton() ).toHaveProperty( 'disabled', ! enabled );
	if ( ! enabled ) {
		expect( mockApiFetch ).not.toHaveBeenCalled();
	}
} );
test.each( [
	[ 'network failure', () => mockFetch.mockRejectedValue( new Error() ) ],
	[
		'malformed response',
		() =>
			mockFetch.mockResolvedValue( {
				json: async () => ( { valid: true } ),
			} ),
	],
] )( 'handles a validation-service %s', async ( name, mockRequest ) => {
	// Arrange.
	arrangeGlobals();
	mockRequest();
	render( <WelcomePage /> );
	// Act.
	await enterLicenseKey();
	// Assert.
	expect( screen.getByText( /Unable to contact/ ) ).toBeInTheDocument();
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
	mockFetch.mockResolvedValueOnce( invalidResponse );
	render( <WelcomePage /> );
	// Act.
	fireEvent.change( licenseKeyInput(), { target: { value: licenseKey } } );
	await enterLicenseKey( licenseKey.replace( '3', '2' ) );
	await act( async () => resolveFirstRequest( validationResponse() ) );
	// Assert.
	expect( screen.getByText( /Please check/ ) ).toBeInTheDocument();
} );
test( 'gives a valid local site precedence over exhausted capacity and saves it', async () => {
	// Arrange.
	arrangeGlobals( { hostname: 'shop.local', isLocalEnvironment: true } );
	mockFetch.mockResolvedValue( validationResponse( 5, 5 ) );
	mockApiFetch.mockResolvedValue( {} );
	render( <WelcomePage /> );
	// Act.
	await enterLicenseKey();
	await act( async () => fireEvent.click( activateButton() ) );
	// Assert.
	expect( mockApiFetch ).toHaveBeenCalledTimes( 1 );
	expect( screen.getByText( /Success!/i ) ).toBeInTheDocument();
} );
test( 'saves an eligible key without revalidating and preserves the success page', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( validationResponse() );
	mockApiFetch.mockResolvedValue( {} );
	render( <WelcomePage /> );
	await enterLicenseKey();
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
	).toHaveAttribute( 'href', productsUrl );
} );
test( 'surfaces a save failure and keeps the validated key eligible', async () => {
	// Arrange.
	arrangeGlobals();
	mockFetch.mockResolvedValue( validationResponse() );
	mockApiFetch.mockRejectedValue( new Error() );
	render( <WelcomePage /> );
	await enterLicenseKey();
	// Act.
	await act( async () => fireEvent.click( activateButton() ) );
	// Assert.
	expect( screen.getByText( /Unable to apply/ ) ).toBeInTheDocument();
	expect( activateButton() ).toBeEnabled();
} );
