import { PanelBody } from '@wordpress/components';
import { PluginSidebar } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import ClearanceIcon from './icon';

const SIDEBAR_NAME = 'wc-clearance-sidebar';

const ClearanceSectionSidebar = () => {
	return (
		<PluginSidebar
			name={ SIDEBAR_NAME }
			title={ __( 'Clearance section', 'wc-clearance' ) }
			isPinnable={ true }
			icon={ ClearanceIcon }
		>
			<PanelBody>
				<p>
					{ __(
						'Customize the appearance of the clearance section. Changes apply to the whole site.',
						'wc-clearance'
					) }
				</p>
				<h2>{ __( 'Badge', 'wc-clearance' ) }</h2>
			</PanelBody>
		</PluginSidebar>
	);
};

registerPlugin( SIDEBAR_NAME, {
	render: ClearanceSectionSidebar,
} );
