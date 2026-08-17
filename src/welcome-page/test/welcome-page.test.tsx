/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode } from 'react';
import { render, screen, act, fireEvent } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { WelcomePage } from '../WelcomePage';
import type { ValidationState } from '../useLicenseValidation';
import { useLicenseValidation } from '../useLicenseValidation';

jest.mock( '@wordpress/api-fetch', () => ( {
	__esModule: true,
	default: jest.fn(),
} ) );

jest.mock( '@wordpress/ui', () => ( { Link: 'a' } ) );

jest.mock( '../useLicenseValidation', () => ( {
	useLicenseValidation: jest.fn(),
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
const mockUseLicenseValidation = jest.mocked( useLicenseValidation );
const mockHandleLicenseKeyChange = jest.fn();

function arrangeGlobals( {
	licenseKey = '',
	productsUrl = '/wp-admin/edit.php?post_type=product',
}: { licenseKey?: string; productsUrl?: string } = {} ) {
	( window as any ).outletproWelcomePage = {
		hostname: 'example.com',
		isLocalHost: '',
		licenseKey,
		productsUrl,
	};
	mockApiFetch.mockReset();
}

function arrangeValidation( {
	licenseKey = '',
	validationState = { status: 'idle' },
	canActivate = false,
}: {
	licenseKey?: string;
	validationState?: ValidationState;
	canActivate?: boolean;
} = {} ) {
	mockUseLicenseValidation.mockReset();
	mockHandleLicenseKeyChange.mockReset();
	mockUseLicenseValidation.mockReturnValue( {
		licenseKey,
		validationState,
		canActivate,
		handleLicenseKeyChange: mockHandleLicenseKeyChange,
	} );
}

test( 'renders the welcome message', () => {
	// Arrange.
	arrangeGlobals();
	arrangeValidation();

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByText( /Thank you for choosing Outlet Pro!/i )
	).toBeInTheDocument();
} );

test( 'renders the license re-setup message for an existing license key', () => {
	// Arrange.
	arrangeGlobals( { licenseKey: 'OLD-LICENSE-KEY' } );
	arrangeValidation();

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByRole( 'heading', { name: 'Outlet Pro Setup' } )
	).toBeInTheDocument();
	expect(
		screen.getByText(
			'The license could not be verified on this site. Enter your premium license key to continue.'
		)
	).toBeInTheDocument();
} );

test( 'renders the license key input', () => {
	// Arrange.
	arrangeGlobals();
	arrangeValidation();

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByLabelText( /Premium license key/i )
	).toBeInTheDocument();
} );

test( 'renders the Activate site button', () => {
	// Arrange.
	arrangeGlobals();
	arrangeValidation();

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeInTheDocument();
} );

test( 'forwards license key changes to validation', () => {
	// Arrange.
	arrangeGlobals();
	arrangeValidation();
	render( <WelcomePage /> );

	// Act.
	fireEvent.change( screen.getByLabelText( /Premium license key/i ), {
		target: { value: 'ABCD-1234' },
	} );

	// Assert.
	expect( mockHandleLicenseKeyChange ).toHaveBeenCalledWith(
		'ABCD-1234',
		false
	);
} );

test( 'forces validation after a license key is pasted', () => {
	// Arrange.
	arrangeGlobals();
	arrangeValidation();
	render( <WelcomePage /> );

	// Act.
	const input = screen.getByLabelText( /Premium license key/i );
	fireEvent.paste( input );
	fireEvent.change( input, { target: { value: 'ABCD-1234' } } );

	// Assert.
	expect( mockHandleLicenseKeyChange ).toHaveBeenCalledWith(
		'ABCD-1234',
		true
	);
} );

test.each< [ string, ValidationState, 'status' | 'alert' ] >( [
	[ 'idle', { status: 'idle' }, 'status' ],
	[ 'validating', { status: 'validating' }, 'status' ],
	[ 'invalid', { status: 'invalid' }, 'alert' ],
	[ 'error', { status: 'error' }, 'alert' ],
	[ 'unavailable', { status: 'unavailable', total: 5 }, 'status' ],
] )(
	'renders the %s validation state with activation disabled',
	( name, validationState, role ) => {
		// Arrange.
		arrangeGlobals();
		arrangeValidation( { validationState } );

		// Act.
		render( <WelcomePage /> );

		// Assert.
		expect( screen.getByRole( role ) ).toBeInTheDocument();
		expect(
			screen.getByRole( 'button', { name: /Activate site/i } )
		).toBeDisabled();
	}
);

test( 'renders the available validation state with activation enabled', () => {
	// Arrange.
	arrangeGlobals();
	arrangeValidation( {
		validationState: { status: 'available', remaining: 3, total: 5 },
		canActivate: true,
	} );

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect( screen.getByRole( 'status' ) ).toBeInTheDocument();
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeEnabled();
} );

test( 'enables activation for an unavailable license on a local host', () => {
	// Arrange.
	arrangeGlobals();
	( window as any ).outletproWelcomePage.hostname = 'shop.local';
	( window as any ).outletproWelcomePage.isLocalHost = '1';
	arrangeValidation( {
		validationState: { status: 'unavailable', total: 5 },
	} );

	// Act.
	render( <WelcomePage /> );

	// Assert.
	expect(
		screen.getByRole( 'button', { name: /Activate site/i } )
	).toBeEnabled();
} );

test( 'shows success message after valid license key is saved', async () => {
	// Arrange.
	arrangeGlobals();
	arrangeValidation( {
		licenseKey: 'ABCD-1234',
		validationState: { status: 'available', remaining: 3, total: 5 },
		canActivate: true,
	} );
	mockApiFetch.mockResolvedValue( {} );

	// Act.
	render( <WelcomePage /> );
	await act( async () => {
		fireEvent.click(
			screen.getByRole( 'button', { name: /Activate site/i } )
		);
	} );

	// Assert.
	expect( screen.getByText( /Success!/i ) ).toBeInTheDocument();
	expect( mockApiFetch ).toHaveBeenCalledWith( {
		path: '/wp/v2/settings',
		method: 'POST',
		data: { outletpro_license_key: 'ABCD-1234' },
	} );
} );

test( 'shows error when REST API save fails', async () => {
	// Arrange.
	arrangeGlobals();
	arrangeValidation( {
		licenseKey: 'ABCD-1234',
		validationState: { status: 'available', remaining: 3, total: 5 },
		canActivate: true,
	} );
	mockApiFetch.mockRejectedValue( new Error( 'Forbidden' ) );

	// Act.
	render( <WelcomePage /> );
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
	arrangeValidation( {
		licenseKey: 'ABCD-1234',
		validationState: { status: 'available', remaining: 3, total: 5 },
		canActivate: true,
	} );
	mockApiFetch.mockResolvedValue( {} );

	// Act.
	render( <WelcomePage /> );
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
