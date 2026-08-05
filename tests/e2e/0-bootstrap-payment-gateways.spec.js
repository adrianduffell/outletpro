/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { test as setup, expect } from '@wordpress/e2e-test-utils-playwright';

setup( 'enable bacs payment gateway', async ( { requestUtils } ) => {
	const gateway = await requestUtils.rest( {
		method: 'PUT',
		path: '/wc/v3/payment_gateways/bacs',
		data: {
			enabled: true,
		},
	} );

	expect( gateway.enabled ).toBe( true );
} );

setup( 'enable cod payment gateway', async ( { requestUtils } ) => {
	const gateway = await requestUtils.rest( {
		method: 'PUT',
		path: '/wc/v3/payment_gateways/cod',
		data: {
			enabled: true,
		},
	} );

	expect( gateway.enabled ).toBe( true );
} );
