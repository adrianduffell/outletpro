/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { store as coreStore } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import useSettings from '../use-settings';

type PaletteColor = { slug: string; color: string };

export default function BadgeColorPalette() {
	const { bgColor, textColor } = useSettings();
	const { base, stylesheet } = useSelect(
		( select ) => ( {
			base: select(
				coreStore
			).__experimentalGetCurrentThemeBaseGlobalStyles(),
			stylesheet: select( coreStore ).getCurrentTheme()?.stylesheet,
		} ),
		[]
	);
	const {
		__experimentalReceiveThemeBaseGlobalStyles:
			receiveThemeBaseGlobalStyles,
	} = useDispatch( coreStore );

	useEffect( () => {
		if ( ! stylesheet ) {
			return;
		}
		const tile = base?.settings?.blocks?.[ 'outletpro/outlet-tile' ];
		const palette = tile?.color?.palette?.theme;
		if ( ! palette ) {
			return;
		}
		const colors = palette.map( ( preset: PaletteColor ) => {
			let color;
			switch ( preset.slug ) {
				case 'outletpro-badge-bg-color':
					if ( bgColor === undefined ) {
						return preset;
					}
					color = bgColor || '#000000';
					break;
				case 'outletpro-badge-text-color':
					if ( textColor === undefined ) {
						return preset;
					}
					color = textColor || '#FFFFFF';
					break;
				default:
					return preset;
			}
			return color === preset.color ? preset : { ...preset, color };
		} );
		if (
			colors.every(
				( color: PaletteColor, index: number ) =>
					color === palette[ index ]
			)
		) {
			return;
		}
		receiveThemeBaseGlobalStyles( stylesheet, {
			...base,
			settings: {
				...base.settings,
				blocks: {
					...base.settings.blocks,
					'outletpro/outlet-tile': {
						...tile,
						color: {
							...tile.color,
							palette: { ...tile.color.palette, theme: colors },
						},
					},
				},
			},
		} );
	}, [ base, stylesheet, bgColor, textColor, receiveThemeBaseGlobalStyles ] );

	return null;
}
