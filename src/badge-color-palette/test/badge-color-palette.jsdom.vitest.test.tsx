/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test, vi } from 'vitest';
import { render } from '@testing-library/react';
import { createRegistry, RegistryProvider } from '@wordpress/data';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import BadgeColorPalette from '../index';

vi.mock( '@wordpress/core-data', async ( importOriginal ) => ( {
	...( await importOriginal< typeof import( '@wordpress/core-data' ) >() ),
	useEntityProp: vi.fn(),
} ) );

test.each( [
	{
		description: 'edited colors',
		background: '#008000',
		text: '#123456',
		expectedBackground: '#008000',
		expectedText: '#123456',
	},
	{
		description: 'cleared colors',
		background: '',
		text: '',
		expectedBackground: '#FFFFFF',
		expectedText: '#000000',
	},
] )( 'updates the palette from $description', ( values ) => {
	// Arrange.
	const registry = createRegistry();
	registry.register( coreStore );
	const data = registry.dispatch( coreStore );
	data.receiveCurrentTheme( { stylesheet: 'test-theme' } );
	data.receiveEntityRecords( 'root', 'theme', { stylesheet: 'test-theme' } );
	const themeColors = [ { slug: 'accent', color: '#0000ff' } ];
	data.__experimentalReceiveThemeBaseGlobalStyles( 'test-theme', {
		settings: {
			color: { palette: { theme: themeColors } },
			blocks: {
				'outletpro/outlet-tile': {
					color: {
						palette: {
							theme: [
								...themeColors,
								{
									slug: 'outletpro-badge-bg-color',
									color: '#ff0000',
								},
								{
									slug: 'outletpro-badge-text-color',
									color: '#ffffff',
								},
							],
						},
					},
				},
			},
		},
	} );
	data.finishResolution( 'getCurrentTheme', [] );
	data.finishResolution(
		'__experimentalGetCurrentThemeBaseGlobalStyles',
		[]
	);
	const settings: Record< string, string | undefined > = {
		outletpro_badge_bg_color: '#ff0000',
		outletpro_badge_text_color: '#ffffff',
	};
	vi.mocked( useEntityProp ).mockImplementation( ( _kind, _name, key ) => [
		settings[ key ],
		vi.fn(),
		null,
	] );
	const preview = (
		<RegistryProvider value={ registry }>
			<BadgeColorPalette />
		</RegistryProvider>
	);
	const { rerender } = render( preview );

	// Act.
	settings.outletpro_badge_bg_color = values.background;
	settings.outletpro_badge_text_color = values.text;
	rerender(
		<RegistryProvider value={ registry }>
			<BadgeColorPalette />
		</RegistryProvider>
	);

	// Assert.
	const base = registry
		.select( coreStore )
		.__experimentalGetCurrentThemeBaseGlobalStyles();
	expect( base.settings.color.palette.theme ).toEqual( themeColors );
	expect(
		base.settings.blocks[ 'outletpro/outlet-tile' ].color.palette.theme
	).toEqual( [
		{ slug: 'accent', color: '#0000ff' },
		{
			slug: 'outletpro-badge-bg-color',
			color: values.expectedBackground,
		},
		{ slug: 'outletpro-badge-text-color', color: values.expectedText },
	] );
} );
