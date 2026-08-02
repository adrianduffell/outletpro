/**
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
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
