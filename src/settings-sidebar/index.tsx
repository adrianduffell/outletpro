/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import {
	BaseControl,
	PanelBody,
	TabPanel,
	TextareaControl,
} from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { settings } from '@wordpress/icons';
import useSettings from '../use-settings';
import BadgeSettings from './BadgeSettings';
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
	const { message, setMessage } = outletSettings;

	const renderMessageSettings = () => (
		<>
			<PanelBody>
				<p
					data-testid="outletpro-message-tab-description"
					style={ { marginBottom: 0 } }
				>
					{ __(
						'Customize the outlet message. Changes apply to the whole site.',
						'outletpro'
					) }
				</p>
			</PanelBody>

			<PanelBody title={ __( 'Message', 'outletpro' ) } initialOpen>
				<BaseControl __nextHasNoMarginBottom={ true }>
					<TextareaControl
						label={ __( 'Message', 'outletpro' ) }
						hideLabelFromVision={ true }
						value={ message ?? '' }
						onChange={ ( value ) => setMessage( value ) }
						rows={ 2 }
						__nextHasNoMarginBottom={ true }
						help={ __(
							'Displayed for outlet products.',
							'outletpro'
						) }
					/>
				</BaseControl>
			</PanelBody>
		</>
	);

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
							return renderMessageSettings();
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
