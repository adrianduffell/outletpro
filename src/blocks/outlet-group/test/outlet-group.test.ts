/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { registerBlockVariation } from '@wordpress/blocks';
import OutletBadgeIcon from '../../outlet-badge/icon';
import { outletGroupVariation } from '../index';

jest.mock( '@wordpress/blocks', () => ( {
	registerBlockVariation: jest.fn(),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( text: string ) => text ),
} ) );

const mockRegisterBlockVariation = jest.mocked( registerBlockVariation );

describe( 'Outlet Group variation', () => {
	test( 'registers the variation for the Group block', () => {
		// Arrange.
		const expectedVariation = {
			name: 'outletpro/outlet-group',
			title: 'Outlet Group',
			description: 'Display blocks only for Outlet products.',
			icon: OutletBadgeIcon,
			attributes: {
				metadata: {
					outletpro: true,
				},
			},
			isActive: [ 'metadata.outletpro' ],
			scope: [ 'block', 'inserter', 'transform' ],
		};

		// Act.
		const registration = mockRegisterBlockVariation.mock.calls[ 0 ];

		// Assert.
		expect( registration ).toEqual( [ 'core/group', expectedVariation ] );
	} );

	test( 'recognizes the variation from the Outlet metadata marker', () => {
		// Arrange.
		const { isActive } = outletGroupVariation;

		// Act.
		const activeAttributes = isActive;

		// Assert.
		expect( activeAttributes ).toEqual( [ 'metadata.outletpro' ] );
	} );
} );
