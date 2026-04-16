import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test( 'enable bank transfer payment gateway', async ( { requestUtils } ) => {
	// Act.
	const gateway = await requestUtils.rest( {
		method: 'PUT',
		path: '/wc/v3/payment_gateways/bacs',
		data: {
			enabled: true,
		},
	} );

	// Assert.
	expect( gateway.enabled ).toBe( true );
} );

test( 'enable cash on delivery payment gateway', async ( { requestUtils } ) => {
	// Act.
	const gateway = await requestUtils.rest( {
		method: 'PUT',
		path: '/wc/v3/payment_gateways/cod',
		data: {
			enabled: true,
		},
	} );

	// Assert.
	expect( gateway.enabled ).toBe( true );
} );
