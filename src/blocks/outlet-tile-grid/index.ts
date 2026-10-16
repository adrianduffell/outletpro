/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { registerBlockType } from '@wordpress/blocks';
import { Edit } from './edit';
import { Save } from './save';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: Edit,
	save: Save,
} );
