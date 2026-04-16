import { test, expect } from '@wordpress/e2e-test-utils-playwright';

// Not a real test. Configures the bank transfer payment gateway on the test site.
test( 'enable bank transfer payment gateway', async ( { requestUtils } ) => {
	const gateway = await requestUtils.rest( {
		method: 'PUT',
		path: '/wc/v3/payment_gateways/bacs',
		data: {
			enabled: true,
		},
	} );

	expect( gateway.enabled ).toBe( true );
} );

// Not a real test. Configures the cash on delivery payment gateway on the test site.
test( 'enable cash on delivery payment gateway', async ( { requestUtils } ) => {
	const gateway = await requestUtils.rest( {
		method: 'PUT',
		path: '/wc/v3/payment_gateways/cod',
		data: {
			enabled: true,
		},
	} );

	expect( gateway.enabled ).toBe( true );
} );
