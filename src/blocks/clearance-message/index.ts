import { registerBlockType } from '@wordpress/blocks';
import { Edit } from './edit';
import metadata from './block.json';
import ClearanceBadgeIcon from './icon';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
	icon: ClearanceBadgeIcon,
} );
