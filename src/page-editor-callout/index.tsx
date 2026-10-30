/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { useSelect } from '@wordpress/data';
import { PluginPostStatusInfo, store as editorStore } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { Icon, external } from '@wordpress/icons';
import useUnsignedIntegerEntityProp from '../use-unsigned-integer-entity-prop';
import { PageEditorCallout } from './callout';

function buildCustomizerUrl( currentUrl: string ): string {
	const customizerUrl = new URL( 'customize.php', currentUrl );
	customizerUrl.searchParams.set( 'autofocus[section]', 'outletpro' );

	return customizerUrl.href;
}

export default function OutletPageEditorCallout(): JSX.Element | null {
	const { currentPostId, isBlockTheme } = useSelect( ( select ) => {
		const editor = select( editorStore );
		const editorSettings = editor.getEditorSettings();

		return {
			currentPostId: editor.getCurrentPostId(),
			isBlockTheme:
				'outletproIsBlockTheme' in editorSettings &&
				editorSettings.outletproIsBlockTheme === true,
		};
	}, [] );
	const [ outletPageId ] =
		useUnsignedIntegerEntityProp( 'outletpro_page_id' );

	if ( isBlockTheme ) {
		return null;
	}
	if ( outletPageId === undefined ) {
		return null;
	}
	if ( currentPostId !== outletPageId ) {
		return null;
	}

	return (
		<PluginPostStatusInfo>
			<PageEditorCallout>
				<p>
					{ __(
						'Manage the outlet badge and message in the Customizer.',
						'outletpro'
					) }
				</p>
				<p>
					<a
						className="outletpro-button-link"
						href={ buildCustomizerUrl( window.location.href ) }
						target="_blank"
						rel="noopener noreferrer"
					>
						{ __( 'Open in customizer', 'outletpro' ) }{ ' ' }
						<Icon
							icon={ external }
							size={ 16 }
							style={ { verticalAlign: '-4px' } }
						/>
					</a>
				</p>
			</PageEditorCallout>
		</PluginPostStatusInfo>
	);
}
