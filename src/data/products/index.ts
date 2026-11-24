/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { store as coreStore } from '@wordpress/core-data';
import { dispatch, select } from '@wordpress/data';
import { PRODUCT_ENTITY } from './product-entity';

export function registerProductEntity( registry = { dispatch, select } ): void {
	const { kind, name } = PRODUCT_ENTITY;
	if (
		registry.select( coreStore ).getEntityConfig( kind, name ) !== undefined
	) {
		return;
	}
	registry.dispatch( coreStore ).addEntities( [ PRODUCT_ENTITY ] );
}
