/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

// WP does not provide types for these packages; treat them as untyped (any).
declare module '@wordpress/blocks';
declare module '@wordpress/block-editor';

// Public Commands APIs used by the outlet manager; the package ships no types.
declare module '@wordpress/commands' {
	import type { ReduxStoreConfig, StoreDescriptor } from '@wordpress/data';

	export const store: StoreDescriptor<
		ReduxStoreConfig<
			unknown,
			{
				open: () => { type: 'OPEN' };
				close: () => { type: 'CLOSE' };
			},
			{ isOpen: ( state: unknown ) => boolean }
		>
	>;
}
