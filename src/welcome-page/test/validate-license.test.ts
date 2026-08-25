/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { validateLicense } from '../validateLicense';

const LICENSE_KEY = '38B1460A-5104-4067-A91D-77B872934D51';
const EXPIRES_AT = '2026-03-25T00:00:00.000000Z';
const PRODUCT_ID = 1279790;
const LICENSE_AVAILABLE = {
	valid: true,
	license_key: {
		activation_limit: 5,
		activation_usage: 2,
		expires_at: EXPIRES_AT,
	},
	meta: { product_id: PRODUCT_ID },
};
const LICENSE_UNLIMITED = {
	valid: true,
	license_key: { activation_limit: null },
	meta: { product_id: PRODUCT_ID },
};
const LICENSE_UNAVAILABLE = {
	valid: true,
	license_key: {
		activation_limit: 5,
		activation_usage: 5,
	},
	meta: { product_id: PRODUCT_ID },
};
const mockFetch = jest.fn();
global.fetch = mockFetch;

test( 'validates a license with available capacity', async () => {
	// Arrange.
	mockFetch.mockReset();
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( LICENSE_AVAILABLE ),
	} );

	// Act.
	const result = await validateLicense( LICENSE_KEY );

	// Assert.
	expect( result ).toEqual( {
		valid: true,
		remaining: 3,
		total: 5,
		expiresAt: EXPIRES_AT,
	} );
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
	expect( request.body.get( 'license_key' ) ).toBe( LICENSE_KEY );
} );

test( 'returns invalid when the license is invalid', async () => {
	// Arrange.
	mockFetch.mockReset();
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( { valid: false } ),
	} );

	// Act.
	const result = await validateLicense( LICENSE_KEY );

	// Assert.
	expect( result ).toEqual( { valid: false } );
} );

test( 'returns expired when the license is expired', async () => {
	// Arrange.
	mockFetch.mockReset();
	mockFetch.mockResolvedValue( {
		json: () =>
			Promise.resolve( {
				valid: false,
				license_key: {
					expires_at: EXPIRES_AT,
					status: 'expired',
				},
			} ),
	} );

	// Act.
	const result = await validateLicense( LICENSE_KEY );

	// Assert.
	expect( result ).toEqual( { valid: false, expiresAt: EXPIRES_AT } );
} );

test.each( [
	[ 'missing', undefined ],
	[ 'invalid', 'not-a-date' ],
] )(
	'rejects an expired license with a %s expiry date',
	async ( name, expiresAt ) => {
		// Arrange.
		mockFetch.mockReset();
		mockFetch.mockResolvedValue( {
			json: () =>
				Promise.resolve( {
					valid: false,
					license_key: {
						expires_at: expiresAt,
						status: 'expired',
					},
				} ),
		} );

		// Expect.
		const validation = expect( validateLicense( LICENSE_KEY ) ).rejects;

		// Act and assert.
		await validation.toThrow( 'Unexpected license validation response' );
	}
);

test( 'returns invalid when the product is not allowed', async () => {
	// Arrange.
	mockFetch.mockReset();
	mockFetch.mockResolvedValue( {
		json: () =>
			Promise.resolve( {
				valid: true,
				meta: { product_id: 1234567 },
			} ),
	} );

	// Act.
	const result = await validateLicense( LICENSE_KEY );

	// Assert.
	expect( result ).toEqual( { valid: false } );
} );

test( 'validates a license with unlimited capacity', async () => {
	// Arrange.
	mockFetch.mockReset();
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( LICENSE_UNLIMITED ),
	} );

	// Act.
	const result = await validateLicense( LICENSE_KEY );

	// Assert.
	expect( result ).toEqual( {
		valid: true,
		remaining: Infinity,
		total: Infinity,
	} );
} );

test( 'validates a license with unavailable capacity', async () => {
	// Arrange.
	mockFetch.mockReset();
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( LICENSE_UNAVAILABLE ),
	} );

	// Act.
	const result = await validateLicense( LICENSE_KEY );

	// Assert.
	expect( result ).toEqual( { valid: true, remaining: 0, total: 5 } );
} );

test( 'does not return negative available capacity', async () => {
	// Arrange.
	mockFetch.mockReset();
	mockFetch.mockResolvedValue( {
		json: () =>
			Promise.resolve( {
				valid: true,
				license_key: {
					activation_limit: 5,
					activation_usage: 6,
				},
				meta: { product_id: PRODUCT_ID },
			} ),
	} );

	// Act.
	const result = await validateLicense( LICENSE_KEY );

	// Assert.
	expect( result ).toEqual( { valid: true, remaining: 0, total: 5 } );
} );

test.each( [
	[ 'valid status', {} ],
	[ 'product ID', { valid: true } ],
	[
		'activation limit',
		{
			valid: true,
			license_key: { activation_usage: 2 },
			meta: { product_id: PRODUCT_ID },
		},
	],
	[
		'activation usage',
		{
			valid: true,
			license_key: { activation_limit: 5 },
			meta: { product_id: PRODUCT_ID },
		},
	],
	[
		'negative activation limit',
		{
			valid: true,
			license_key: { activation_limit: -1, activation_usage: 0 },
			meta: { product_id: PRODUCT_ID },
		},
	],
	[
		'fractional activation usage',
		{
			valid: true,
			license_key: { activation_limit: 5, activation_usage: 1.5 },
			meta: { product_id: PRODUCT_ID },
		},
	],
	[
		'expiry date',
		{
			valid: true,
			license_key: {
				activation_limit: 5,
				activation_usage: 2,
				expires_at: 'not-a-date',
			},
			meta: { product_id: PRODUCT_ID },
		},
	],
] )( 'rejects a response with an unexpected %s', async ( name, response ) => {
	// Arrange.
	mockFetch.mockReset();
	mockFetch.mockResolvedValue( {
		json: () => Promise.resolve( response ),
	} );

	// Expect.
	const validation = expect( validateLicense( LICENSE_KEY ) ).rejects;

	// Act and assert.
	await validation.toThrow( 'Unexpected license validation response' );
} );

test( 'propagates request failures', async () => {
	// Arrange.
	mockFetch.mockReset();
	mockFetch.mockRejectedValue( new Error( 'Network error' ) );

	// Expect.
	const validation = expect( validateLicense( LICENSE_KEY ) ).rejects;

	// Act and assert.
	await validation.toThrow( 'Network error' );
} );
