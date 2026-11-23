/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { expect, test } from 'vitest';
import { excludeBadgeColorPresets } from '../exclude-badge-color-presets';

test( 'excludes badge presets from each palette', () => {
	// Arrange.
	const accent = { name: 'Accent', slug: 'accent', color: '#123456' };
	const custom = { name: 'Custom red', color: '#ff0000' };
	const badgeBackground = {
		name: 'Outlet badge background color',
		slug: 'outletpro-badge-bg-color',
		color: 'var(--outletpro-badge-bg-color)',
	};
	const badgeText = {
		name: 'Outlet badge text color',
		slug: 'outletpro-badge-text-color',
		color: 'var(--outletpro-badge-text-color)',
	};
	const palettes = [
		{ name: 'Theme', colors: [ accent, badgeBackground, badgeText ] },
		{ name: 'Custom', colors: [ custom, badgeText ] },
		{ name: 'Badge', colors: [ badgeBackground, badgeText ] },
	];

	// Act.
	const result = excludeBadgeColorPresets( palettes );

	// Assert.
	expect( result ).toEqual( [
		{ name: 'Theme', colors: [ accent ] },
		{ name: 'Custom', colors: [ custom ] },
	] );
} );
