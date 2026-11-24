/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { store as commandsStore } from '@wordpress/commands';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import { store as noticesStore } from '@wordpress/notices';
import { vi } from 'vitest';
import { createSettingsRegistry } from '../use-settings/test-utils';

export const currency = {
	symbol: '£',
	priceFormat: '%1$s%2$s',
	thousandSeparator: ',',
};

export function createEditorRegistry( settings?: Record< string, unknown > ) {
	vi.stubGlobal( 'wc', { wcSettings: { CURRENCY: currency } } );
	const { registry, wrapper } = createSettingsRegistry( settings, {
		outletpro_markdown_levels: 'Markdown Levels',
		outletpro_price_levels: 'Price Levels',
	} );
	registry.register( commandsStore );
	registry.register( blockEditorStore );
	registry.register( noticesStore );
	registry
		.dispatch( coreStore )
		.receiveEntityRecords( 'root', '__unstableBase', { home: '/' } );
	registry
		.dispatch( coreStore )
		.finishResolution( 'getEntityRecord', [
			'root',
			'__unstableBase',
			undefined,
		] );
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
