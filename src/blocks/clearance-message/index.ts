import { registerBlockType } from '@wordpress/blocks';
import { Edit } from './edit';
import metadata from './block.json';
import ClearanceMessageIcon from './icon';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
	icon: ClearanceMessageIcon,
} );
