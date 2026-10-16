/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { createReduxStore, register } from '@wordpress/data';
import * as selectors from './selectors';

export const store = createReduxStore( 'outletpro', {
	reducer: () => null,
	selectors,
} );

register( store );
