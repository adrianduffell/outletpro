/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { TabPanel } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { settings } from '@wordpress/icons';
import useSettings from '../use-settings';
import BadgeSettings from './BadgeSettings';
import MessageSettings from './MessageSettings';
import { COMPLEMENTARY_AREA, SIDEBAR_NAME, QUERY_PARAM } from './constants';

const withSiteRecord = ( Component: React.ComponentType ) => () => {
	const hasSiteRecord = useSelect(
		( select ) => !! select( coreStore ).getEntityRecord( 'root', 'site' ),
		[]
	);

	return hasSiteRecord ? <Component /> : null;
};

const SettingsSidebar = () => {
	const { enableComplementaryArea } = useDispatch( 'core/interface' );

	useEffect( () => {
		const query = new URLSearchParams( window.location.search );
		if ( query.get( QUERY_PARAM ) !== '1' ) {
			return;
		}

		enableComplementaryArea( 'core', COMPLEMENTARY_AREA );
	}, [ enableComplementaryArea ] );

	const outletSettings = useSettings();

	return (
		<>
			<PluginSidebarMoreMenuItem
				target={ SIDEBAR_NAME }
				icon={ settings }
			>
				{ __( 'Outlet settings', 'outletpro' ) }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				name={ SIDEBAR_NAME }
				title={ __( 'Outlet settings', 'outletpro' ) }
				isPinnable={ false }
				icon={ settings }
				className="outletpro-sidebar"
			>
				<TabPanel
					className="outletpro-sidebar__tabs"
					activeClass="is-active"
					tabs={ [
						{
							name: 'badge',
							title: __( 'Badge', 'outletpro' ),
							className: 'outletpro-sidebar__tab',
						},
						{
							name: 'message',
							title: __( 'Message', 'outletpro' ),
							className: 'outletpro-sidebar__tab',
						},
					] }
				>
					{ ( tab ) => {
						if ( tab.name === 'message' ) {
							return (
								<MessageSettings settings={ outletSettings } />
							);
						}
						return <BadgeSettings settings={ outletSettings } />;
					} }
				</TabPanel>
			</PluginSidebar>
		</>
	);
};

const isSiteEditor = window.location.pathname.includes( 'site-editor.php' );

// Only register the sidebar in the Site Editor (site-editor.php).
if ( isSiteEditor ) {
	registerPlugin( SIDEBAR_NAME, {
		render: withSiteRecord( SettingsSidebar ),
	} );
}
