/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */
import { render, screen } from '@testing-library/react';
import { ValidationMessage, type ValidationState } from '../ValidationMessage';
jest.mock( '@wordpress/ui', () => ( { Link: 'a' } ) );
const helpUrl = 'https://outletpro.zip/help/license-key';

function renderMessage(
	validationState: ValidationState,
	isLocalHost = false
) {
	render(
		<p role="status">
			<ValidationMessage
				hostname="shop.local"
				isLocalHost={ isLocalHost }
				validationState={ validationState }
			/>
		</p>
	);
}

const messages: [ string, ValidationState, string, boolean ][] = [
	[ 'validating', { status: 'validating' }, 'Validating…', true ],
	[
		'invalid',
		{ status: 'invalid' },
		'Please check your premium license key and try again.',
		true,
	],
	[
		'service error',
		{ status: 'error' },
		'Unable to contact the licensing service. Please try again.',
		true,
	],
	[
		'singular availability',
		{ status: 'available', remaining: 1, total: 1 },
		'✅ 1 site activation available',
		false,
	],
	[
		'plural availability',
		{ status: 'available', remaining: 3, total: 5 },
		'✅ 3 of 5 site activations available',
		false,
	],
	[
		'unlimited availability',
		{ status: 'available', remaining: Infinity, total: Infinity },
		'✅ Unlimited site activations available',
		false,
	],
];
test.each( messages )(
	'renders the %s message',
	( name, validationState, expected, isLocalHost ) => {
		// Arrange.
		// Act.
		renderMessage( validationState, isLocalHost );
		// Assert.
		expect( screen.getByRole( 'status' ) ).toHaveTextContent( expected );
	}
);
const linkedMessages: [ string, ValidationState, string, boolean ][] = [
	[
		'singular unavailable capacity',
		{ status: 'unavailable', total: 1 },
		'❌ License has reached the site activation limit. Purchase another license or deactivate the existing site to use this license. Learn more',
		false,
	],
	[
		'plural unavailable capacity',
		{ status: 'unavailable', total: 5 },
		'❌ License has reached the 5-site activation limit. Purchase another license or deactivate a site to use this license. Learn more',
		false,
	],
	[
		'local available capacity',
		{ status: 'available', remaining: 3, total: 5 },
		'✅ shop.local License includes unlimited local sites. Learn more',
		true,
	],
	[
		'local unlimited capacity',
		{ status: 'available', remaining: Infinity, total: Infinity },
		'✅ shop.local License includes unlimited local sites. Learn more',
		true,
	],
	[
		'local unavailable capacity',
		{ status: 'unavailable', total: 5 },
		'✅ shop.local License includes unlimited local sites. Learn more',
		true,
	],
];
test.each( linkedMessages )(
	'renders the %s message and help link',
	( name, validationState, expected, isLocalHost ) => {
		// Arrange.
		// Act.
		renderMessage( validationState, isLocalHost );
		// Assert.
		const link = screen.getByRole( 'link', { name: 'Learn more' } );
		expect( link.parentElement ).toHaveTextContent( expected );
		expect( link ).toHaveAttribute( 'href', helpUrl );
	}
);
test( 'renders the local hostname as code', () => {
	// Arrange.
	// Act.
	renderMessage( { status: 'available', remaining: 3, total: 5 }, true );
	// Assert.
	expect(
		screen.getByText( 'shop.local', { selector: 'code' } )
	).toBeInTheDocument();
} );
test( 'renders the default license links on a local host', () => {
	// Arrange.
	// Act.
	renderMessage( { status: 'idle' }, true );
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
