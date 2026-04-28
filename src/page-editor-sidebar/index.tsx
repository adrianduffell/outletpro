import { PluginSidebar } from '@wordpress/edit-post';
import { registerPlugin } from '@wordpress/plugins';
import ClearanceIcon from './icon';

const PLUGIN_NAME = 'wc-clearance-sidebar';

const ClearanceSidebar = () => {
	return (
		<PluginSidebar
			name={ PLUGIN_NAME }
			title="Clearance section settings"
			isPinnable={ true }
			icon={ ClearanceIcon }
		/>
	);
};

registerPlugin( PLUGIN_NAME, {
	render: ClearanceSidebar,
} );
