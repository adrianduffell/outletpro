/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { registerBlockType } from '@wordpress/blocks';
import { Edit } from './edit';
import metadata from './block.json';
import OutletBadgeIcon from './icon';
import './style.css';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
	icon: OutletBadgeIcon,
} );
