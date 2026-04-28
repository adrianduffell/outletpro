import { PluginSidebar } from '@wordpress/edit-post';
import { registerPlugin } from '@wordpress/plugins';
import ClearanceIcon from './icon';

const SIDEBAR_NAME = 'wc-clearance-sidebar';

const ClearanceSectionSidebar = () => {
	return (
		<PluginSidebar
			name={ SIDEBAR_NAME }
			title="Clearance section"
			isPinnable={ true }
			icon={ ClearanceIcon }
		/>
	);
};

registerPlugin( SIDEBAR_NAME, {
	render: ClearanceSectionSidebar,
} );
