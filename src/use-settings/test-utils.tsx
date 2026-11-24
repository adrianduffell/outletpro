/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import type { ReactNode } from 'react';
import { store as coreStore } from '@wordpress/core-data';
import { createRegistry, RegistryProvider } from '@wordpress/data';

export function createSettingsRegistry(
	settings?: Record< string, unknown >,
	labels: Record< string, string > = {}
) {
	const registry = createRegistry();
	registry.register( coreStore );
	registry.dispatch( coreStore ).addEntities( [
		{
			kind: 'root',
			name: 'site',
			label: 'Site',
			key: false,
			baseURL: '/wp/v2/settings',
			meta: { labels },
		},
	] );
	registry
		.dispatch( coreStore )
		.finishResolution( 'getEntitiesConfig', [ 'root' ] );
	if ( settings !== undefined ) {
		registry
			.dispatch( coreStore )
			.receiveEntityRecords( 'root', 'site', settings );
		registry
			.dispatch( coreStore )
			.finishResolution( 'getEntityRecord', [
				'root',
				'site',
				undefined,
			] );
	}
	function Wrapper( { children }: { children: ReactNode } ) {
		return (
			<RegistryProvider value={ registry }>{ children }</RegistryProvider>
		);
	}
	return { registry, wrapper: Wrapper };
}
