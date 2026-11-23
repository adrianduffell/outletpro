/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

type Palette = {
	name: string;
	colors: { name: string; color: string; slug?: string }[];
};

export function excludeBadgeColorPresets( palettes: Palette[] ): Palette[] {
	return palettes
		.map( ( palette ) => ( {
			...palette,
			colors: palette.colors.filter(
				( { slug } ) =>
					! [
						'outletpro-badge-bg-color',
						'outletpro-badge-text-color',
					].includes( slug ?? '' )
			),
		} ) )
		.filter( ( { colors } ) => colors.length > 0 );
}
