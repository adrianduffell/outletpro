/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { outletGroupVariation } from '../index';

jest.mock( '@wordpress/blocks', () => ( {
	registerBlockVariation: jest.fn(),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( text: string ) => text ),
} ) );

describe( 'Outlet Group variation', () => {
	test( 'activates the variation with the metadata field', () => {
		// Arrange.
		const { isActive } = outletGroupVariation;
		const blockAttributes = {
			metadata: {
				outletpro: true,
			},
		};

		// Act.
		const isOutletGroup = isActive( blockAttributes );

		// Assert.
		expect( isOutletGroup ).toBe( true );
	} );

	test( 'does not activate the variation when the metadata field is false', () => {
		// Arrange.
		const { isActive } = outletGroupVariation;
		const blockAttributes = {
			metadata: {
				outletpro: false,
			},
		};

		// Act.
		const isOutletGroup = isActive( blockAttributes );

		// Assert.
		expect( isOutletGroup ).toBe( false );
	} );
} );
