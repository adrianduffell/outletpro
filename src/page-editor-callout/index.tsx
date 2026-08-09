/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { useSelect } from '@wordpress/data';
import { PluginPostStatusInfo, store as editorStore } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { Icon, external, settings as SettingsIcon } from '@wordpress/icons';
import { QUERY_PARAM as SETTINGS_SIDEBAR_QUERY_PARAM } from '../settings-sidebar/constants';
import useUnsignedIntegerEntityProp from '../use-unsigned-integer-entity-prop';
import './style.css';

const WOOCOMMERCE_CART_TEMPLATE_ID = 'woocommerce/woocommerce//page-cart';

function buildSiteEditorUrl( currentUrl: string ): string {
	const siteEditorUrl = new URL( 'site-editor.php', currentUrl );

	siteEditorUrl.search = new URLSearchParams( {
		postType: 'wp_template',
		postId: WOOCOMMERCE_CART_TEMPLATE_ID,
		canvas: 'edit',
		[ SETTINGS_SIDEBAR_QUERY_PARAM ]: '1',
	} ).toString();

	return siteEditorUrl.href;
}

function buildCustomizerUrl( currentUrl: string, cartUrl: string ): string {
	const customizerUrl = new URL( 'customize.php', currentUrl );
	customizerUrl.searchParams.set( 'autofocus[section]', 'outletpro' );
	customizerUrl.searchParams.set( 'url', cartUrl );

	return customizerUrl.href;
}

export default function OutletPageEditorCallout(): JSX.Element | null {
	const { currentPostId, isBlockTheme, cartUrl } = useSelect( ( select ) => {
		const editor = select( editorStore );
		const editorSettings = editor.getEditorSettings();

		return {
			currentPostId: editor.getCurrentPostId(),
			isBlockTheme:
				'outletproIsBlockTheme' in editorSettings &&
				editorSettings.outletproIsBlockTheme === true,
			cartUrl:
				'outletproCartUrl' in editorSettings &&
				typeof editorSettings.outletproCartUrl === 'string'
					? editorSettings.outletproCartUrl
					: '',
		};
	}, [] );
	const [ outletPageId ] =
		useUnsignedIntegerEntityProp( 'outletpro_page_id' );

	if ( currentPostId !== outletPageId || outletPageId === undefined ) {
		return null;
	}

	const settingsUrl = isBlockTheme
		? buildSiteEditorUrl( window.location.href )
		: buildCustomizerUrl( window.location.href, cartUrl );

	return (
		<PluginPostStatusInfo>
			<div className="outletpro-page-editor-callout">
				<span
					className="outletpro-page-editor-callout__icon"
					role="img"
					aria-label={ __( 'Outlet badge', 'outletpro' ) }
				>
					{ SettingsIcon }
				</span>
				<h3>{ __( 'Outlet settings', 'outletpro' ) }</h3>

				<p>
					{ isBlockTheme
						? __(
								'Manage the outlet badge and message with the outlet settings panel in the site editor.',
								'outletpro'
						  )
						: __(
								'Manage the outlet badge and message in the Customizer.',
								'outletpro'
						  ) }{ ' ' }
				</p>
				<p>
					<a
						className={ 'outletpro-button-link' }
						href={ settingsUrl }
						target="_blank"
						rel="noopener noreferrer"
					>
						{ isBlockTheme
							? __( 'Open in site editor', 'outletpro' )
							: __( 'Open in customizer', 'outletpro' ) }{ ' ' }
						<Icon
							icon={ external }
							size={ 16 }
							style={ { verticalAlign: '-4px' } }
						/>
					</a>
				</p>
			</div>
		</PluginPostStatusInfo>
	);
}
