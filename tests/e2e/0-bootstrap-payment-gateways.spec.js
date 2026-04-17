import { test as setup, expect } from '@wordpress/e2e-test-utils-playwright';

setup( 'enable bank transfer payment gateway', async ( { requestUtils } ) => {
	const gateway = await requestUtils.rest( {
		method: 'PUT',
		path: '/wc/v3/payment_gateways/bacs',
		data: {
			enabled: true,
		},
	} );

	expect( gateway.enabled ).toBe( true );
} );

setup( 'enable cash on delivery payment gateway', async ( { requestUtils } ) => {
	const gateway = await requestUtils.rest( {
		method: 'PUT',
		path: '/wc/v3/payment_gateways/cod',
		data: {
			enabled: true,
		},
	} );

	expect( gateway.enabled ).toBe( true );
} );
