/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { render, screen } from '@testing-library/react';
import { ValidationMessage, type ValidationState } from '../ValidationMessage';

const helpUrl = 'https://outletpro.zip/help/license-key';

test( 'renders every validation message and link', () => {
	// Arrange.
	const { container, rerender } = render(
		<ValidationMessage validationState={ { status: 'idle' } } hostname="" />
	);
	const cases: [ ValidationState, string, string? ][] = [
		[ { status: 'validating' }, 'Validating...' ],
		[
			{ status: 'invalid' },
			'Please check your premium license key and try again.',
		],
		[
			{ status: 'error' },
			'Unable to contact the licensing service. Please try again.',
		],
		[
			{ status: 'available', remaining: 1, total: 1 },
			'✅ 1 site activation available',
		],
		[
			{ status: 'available', remaining: 3, total: 5 },
			'✅ 3 of 5 site activations available',
		],
		[ { status: 'unlimited' }, '✅ Unlimited site activations available' ],
		[
			{ status: 'exhausted', total: 1 },
			'❌ License has reached the site activation limit.',
		],
		[
			{ status: 'exhausted', total: 5 },
			'❌ License has reached the 5-site activation limit.',
		],
		[
			{ status: 'local' },
			'🌐 shop.local License includes unlimited local sites.',
			'shop.local',
		],
	];

	// Assert.
	expect( container ).toHaveTextContent( 'Need a premium license?' );
	expect(
		screen.getByRole( 'link', { name: 'Purchase a license' } )
	).toHaveAttribute( 'href', 'https://outletpro.zip/buy' );
	expect(
		screen.getByRole( 'link', { name: 'find your license key' } )
	).toHaveAttribute( 'href', helpUrl );

	for ( const [ validationState, message, hostname = '' ] of cases ) {
		// Act.
		rerender(
			<ValidationMessage
				validationState={ validationState }
				hostname={ hostname }
			/>
		);

		// Assert.
		expect( container ).toHaveTextContent( message );
	}

	expect(
		screen.getByRole( 'link', { name: 'Learn more' } )
	).toHaveAttribute( 'href', helpUrl );
} );
