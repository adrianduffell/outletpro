/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */
import { render, screen } from '@testing-library/react';
import { ValidationMessage, type ValidationState } from '../ValidationMessage';
jest.mock( '@wordpress/ui', () => ( { Link: 'a' } ) );
const helpUrl = 'https://outletpro.zip/help/license-key';
const expiryHelpUrl = 'https://outletpro.zip/help/license-expiry';
const availableExpiresAt = '2030-09-25T00:00:00.000000Z';
const localizedAvailableExpiry = new Intl.DateTimeFormat( undefined, {
	day: 'numeric',
	month: 'long',
	year: 'numeric',
} ).format( new Date( availableExpiresAt ) );

function renderMessage( validationState: ValidationState ) {
	render(
		<p role="status">
			<ValidationMessage validationState={ validationState } />
		</p>
	);
}

const messages: [ string, ValidationState, string ][] = [
	[ 'validating', { status: 'validating' }, 'Validating…' ],
	[
		'invalid',
		{ status: 'invalid' },
		'Please check your premium license key and try again.',
	],
	[
		'service error',
		{ status: 'error' },
		'Unable to contact the licensing service. Please try again.',
	],
	[
		'singular availability',
		{ status: 'available', remaining: 1, total: 1 },
		'✅ 1 site activation available',
	],
	[
		'plural availability',
		{ status: 'available', remaining: 3, total: 5 },
		'✅ 3 of 5 site activations available',
	],
	[
		'unlimited availability',
		{ status: 'available', remaining: Infinity, total: Infinity },
		'✅ Unlimited site activations available',
	],
	[
		'singular availability with expiry',
		{
			status: 'available',
			remaining: 1,
			total: 1,
			expiresAt: availableExpiresAt,
		},
		`✅ 1 site activation available. Expires ${ localizedAvailableExpiry }`,
	],
	[
		'plural availability with expiry',
		{
			status: 'available',
			remaining: 15,
			total: 25,
			expiresAt: availableExpiresAt,
		},
		`✅ 15 of 25 site activations available. Expires ${ localizedAvailableExpiry }`,
	],
	[
		'unlimited availability with expiry',
		{
			status: 'available',
			remaining: Infinity,
			total: Infinity,
			expiresAt: availableExpiresAt,
		},
		`✅ Unlimited site activations available. Expires ${ localizedAvailableExpiry }`,
	],
];
test.each( messages )(
	'renders the %s message',
	( name, validationState, expected ) => {
		// Arrange.
		// Act.
		renderMessage( validationState );
		// Assert.
		expect( screen.getByRole( 'status' ) ).toHaveTextContent( expected );
	}
);
test( 'renders an expired license with a browser-localized date', () => {
	// Arrange.
	const formatDate = jest
		.spyOn( Date.prototype, 'toLocaleDateString' )
		.mockReturnValue( '25 March, 2026' );

	// Act.
	renderMessage( {
		status: 'expired',
		expiresAt: '2026-03-25T00:00:00.000000Z',
	} );

	// Assert.
	expect( screen.getByRole( 'status' ) ).toHaveTextContent(
		'❌ License expired on 25 March, 2026. Learn more'
	);
	expect(
		screen.getByRole( 'link', { name: 'Learn more' } )
	).toHaveAttribute( 'href', expiryHelpUrl );
	expect( formatDate ).toHaveBeenCalledWith( undefined, {
		day: 'numeric',
		month: 'long',
		year: 'numeric',
	} );
	formatDate.mockRestore();
} );
const linkedMessages: [ string, ValidationState, string ][] = [
	[
		'singular unavailable capacity',
		{ status: 'unavailable', total: 1 },
		'❌ License has reached the site activation limit. Purchase another license or deactivate the existing site to use this license. Learn more',
	],
	[
		'plural unavailable capacity',
		{ status: 'unavailable', total: 5 },
		'❌ License has reached the 5-site activation limit. Purchase another license or deactivate a site to use this license. Learn more',
	],
];
test.each( linkedMessages )(
	'renders the %s message and help link',
	( name, validationState, expected ) => {
		// Arrange.
		// Act.
		renderMessage( validationState );
		// Assert.
		const link = screen.getByRole( 'link', { name: 'Learn more' } );
		expect( link.parentElement ).toHaveTextContent( expected );
		expect( link ).toHaveAttribute( 'href', helpUrl );
	}
);
test( 'renders the default license links', () => {
	// Arrange.
	// Act.
	renderMessage( { status: 'idle' } );
	// Assert.
	expect( screen.getByRole( 'status' ) ).toHaveTextContent(
		'Need a premium license? Purchase a license or find your license key'
	);
	expect(
		screen.getByRole( 'link', { name: 'Purchase a license' } )
	).toHaveAttribute( 'href', 'https://outletpro.zip/buy' );
	expect(
		screen.getByRole( 'link', { name: 'find your license key' } )
	).toHaveAttribute( 'href', helpUrl );
} );
