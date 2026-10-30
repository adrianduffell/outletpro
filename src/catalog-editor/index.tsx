/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import useUnsignedIntegerEntityProp from '../use-unsigned-integer-entity-prop';
import { PageEditorCallout } from '../page-editor-callout/callout';
import { CatalogPageDescription } from './description';
import { CatalogTemplateLink } from './template-link';

export default function CatalogEditor(): JSX.Element | null {
	const { postId, postType, isBlockTheme, templateId } = useSelect(
		( select ) => {
			const editor = select( editorStore );
			const settings = editor.getEditorSettings();
			return {
				postId: editor.getCurrentPostId(),
				postType: editor.getCurrentPostType(),
				templateId: editor.getCurrentTemplateId(),
				isBlockTheme:
					'outletproIsBlockTheme' in settings &&
					settings.outletproIsBlockTheme === true,
			};
		},
		[]
	);
	const [ outletPageId ] =
		useUnsignedIntegerEntityProp( 'outletpro_page_id' );
	if ( ! isBlockTheme ) {
		return null;
	}
	if ( postType !== 'page' ) {
		return null;
	}
	if ( outletPageId === undefined ) {
		return null;
	}
	if ( postId !== outletPageId ) {
		return null;
	}
	return (
		<CatalogPageDescription>
			{ templateId !== null && (
				<PageEditorCallout>
					<CatalogTemplateLink
						templateId={ templateId }
						currentUrl={ window.location.href }
					/>
				</PageEditorCallout>
			) }
		</CatalogPageDescription>
	);
}
