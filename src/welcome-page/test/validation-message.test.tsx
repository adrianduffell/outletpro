/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */
import { render, screen } from '@testing-library/react';
import { ValidationMessage, type ValidationState } from '../ValidationMessage';
jest.mock( '@wordpress/ui', () => ( { Link: 'a' } ) );
const helpUrl = 'https://outletpro.zip/help/license-key';

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
