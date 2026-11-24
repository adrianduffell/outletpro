/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { store as commandsStore } from '@wordpress/commands';
import { store as coreStore } from '@wordpress/core-data';
import { createSettingsRegistry } from '../use-settings/test-utils';

export function createEditorRegistry( settings?: Record< string, unknown > ) {
	const { registry, wrapper } = createSettingsRegistry( settings );
	registry.register( commandsStore );
	registry.dispatch( coreStore ).addEntities( [
		{
			kind: 'postType',
			name: 'page',
			label: 'Pages',
			baseURL: '/wp/v2/pages',
			baseURLParams: { context: 'edit' },
			rawAttributes: [ 'title' ],
			getTitle: ( record: { title: string } ) => record.title,
		},
	] );
	registry
		.dispatch( coreStore )
		.finishResolution( 'getEntitiesConfig', [ 'postType' ] );
	return { registry, wrapper };
}
