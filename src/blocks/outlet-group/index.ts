/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { registerBlockVariation } from '@wordpress/blocks';
import { select } from '@wordpress/data';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

type GroupBlockAttributes = {
	layout?: Record< string, unknown >;
};

type LayoutSettings = Record< string, unknown > | undefined;

export const disableOutletGroupLayoutEditing = (
	layoutSettings: LayoutSettings,
	settingPath: string,
	blockName: string | undefined,
	blockAttributes: GroupBlockAttributes | null | undefined
): LayoutSettings => {
	if ( 'layout' !== settingPath ) {
		return layoutSettings;
	}

	if ( 'core/group' !== blockName ) {
		return layoutSettings;
	}

	if ( true !== blockAttributes?.layout?.outletpro ) {
		return layoutSettings;
	}

	return {
		...( layoutSettings ?? {} ),
		allowEditing: false,
	};
};

export const outletGroupVariation = {
	name: 'outletpro/outlet-group',
	title: __( 'Outlet Group', 'outletpro' ),
	description: __(
		'Conditionally displays contents for outlet products only.',
		'outletpro'
	),
	attributes: {
		// Skip the Group layout picker without applying any visual styles.
		style: {},
		layout: {
			type: 'default',
			outletpro: true,
		},
	},
	isActive: ( blockAttributes: GroupBlockAttributes ): boolean =>
		blockAttributes.layout?.outletpro === true,
	scope: [ 'block', 'inserter', 'transform' ],
};

registerBlockVariation( 'core/group', outletGroupVariation );

addFilter(
	'blockEditor.useSetting.before',
	'outletpro/outlet-group-layout-editing',
	(
		layoutSettings: LayoutSettings,
		settingPath: string,
		clientId: string | undefined,
		blockName: string | undefined
	): LayoutSettings => {
		if ( undefined === clientId ) {
			return layoutSettings;
		}

		const blockAttributes =
			select( 'core/block-editor' ).getBlockAttributes( clientId );

		return disableOutletGroupLayoutEditing(
			layoutSettings,
			settingPath,
			blockName,
			blockAttributes
		);
	}
);
