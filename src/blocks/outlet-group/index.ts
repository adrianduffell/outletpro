/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

export const outletGroupVariation = {
	name: 'outletpro/outlet-group',
	title: __( 'Outlet Group', 'outletpro' ),
	description: __(
		'Conditionally displays contents for outlet products only.',
		'outletpro'
	),
	attributes: {
		metadata: {
			outletpro: true,
		},
	},
	isActive: ( blockAttributes: {
		metadata?: { outletpro?: unknown };
	} ): boolean => blockAttributes.metadata?.outletpro === true,
};

registerBlockVariation( 'core/group', outletGroupVariation );
