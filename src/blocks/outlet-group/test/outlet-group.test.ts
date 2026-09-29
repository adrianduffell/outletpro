/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	disableOutletGroupLayoutEditing,
	outletGroupVariation,
} from '../index';

jest.mock( '@wordpress/blocks', () => ( {
	registerBlockVariation: jest.fn(),
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: jest.fn( ( text: string ) => text ),
} ) );

describe( 'Outlet Group variation', () => {
	test( 'activates the variation with the Outlet layout field', () => {
		// Arrange.
		const { isActive } = outletGroupVariation;
		const blockAttributes = {
			layout: {
				type: 'default',
				outletpro: true,
			},
		};

		// Act.
		const isOutletGroup = isActive( blockAttributes );

		// Assert.
		expect( isOutletGroup ).toBe( true );
	} );

	test( 'does not activate the variation without the Outlet layout field', () => {
		// Arrange.
		const { isActive } = outletGroupVariation;
		const blockAttributes = {
			layout: {
				type: 'default',
			},
		};

		// Act.
		const isOutletGroup = isActive( blockAttributes );

		// Assert.
		expect( isOutletGroup ).toBe( false );
	} );

	test( 'does not remain active after changing to a core layout variation', () => {
		// Arrange.
		const { attributes, isActive } = outletGroupVariation;
		const rowVariationAttributes = {
			layout: {
				type: 'flex',
				flexWrap: 'nowrap',
			},
		};

		// Act.
		const transformedAttributes = {
			...attributes,
			...rowVariationAttributes,
		};

		// Assert.
		expect( isActive( transformedAttributes ) ).toBe( false );
	} );
} );

describe( 'Outlet Group layout editing', () => {
	test( 'disables layout editing for the Outlet Group', () => {
		// Arrange.
		const layoutSettings = {
			allowInheriting: true,
		};
		const blockAttributes = {
			layout: {
				type: 'default',
				outletpro: true,
			},
		};

		// Act.
		const filteredLayoutSettings = disableOutletGroupLayoutEditing(
			layoutSettings,
			'layout',
			'core/group',
			blockAttributes
		);

		// Assert.
		expect( filteredLayoutSettings ).toEqual( {
			allowEditing: false,
			allowInheriting: true,
		} );
	} );

	test( 'retains layout editing for a regular Group', () => {
		// Arrange.
		const layoutSettings = {
			allowEditing: true,
		};
		const blockAttributes = {
			layout: {
				type: 'default',
			},
		};

		// Act.
		const filteredLayoutSettings = disableOutletGroupLayoutEditing(
			layoutSettings,
			'layout',
			'core/group',
			blockAttributes
		);

		// Assert.
		expect( filteredLayoutSettings ).toBe( layoutSettings );
	} );

	test( 'does not affect other Outlet Group settings', () => {
		// Arrange.
		const spacingSettings = {
			blockGap: true,
		};
		const blockAttributes = {
			layout: {
				type: 'default',
				outletpro: true,
			},
		};

		// Act.
		const filteredSpacingSettings = disableOutletGroupLayoutEditing(
			spacingSettings,
			'spacing',
			'core/group',
			blockAttributes
		);

		// Assert.
		expect( filteredSpacingSettings ).toBe( spacingSettings );
	} );

	test( 'does not affect layout editing for another block type', () => {
		// Arrange.
		const layoutSettings = {
			allowEditing: true,
		};
		const blockAttributes = {
			layout: {
				type: 'default',
				outletpro: true,
			},
		};

		// Act.
		const filteredLayoutSettings = disableOutletGroupLayoutEditing(
			layoutSettings,
			'layout',
			'core/columns',
			blockAttributes
		);

		// Assert.
		expect( filteredLayoutSettings ).toBe( layoutSettings );
	} );
} );
