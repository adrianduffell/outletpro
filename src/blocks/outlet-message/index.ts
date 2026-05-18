import { registerBlockType } from '@wordpress/blocks';
import { Edit } from './edit';
import metadata from './block.json';
import OutletMessageIcon from './icon';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
	icon: OutletMessageIcon,
} );
