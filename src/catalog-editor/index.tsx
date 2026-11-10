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
	const { postId, postType, isBlockTheme, templateId, templatePartId } =
		useSelect( ( select ) => {
			const editor = select( editorStore );
			const settings = editor.getEditorSettings();
			let resolvedTemplateId =
				'outletproCatalogTemplateId' in settings
					? settings.outletproCatalogTemplateId
					: undefined;
			if ( editor.getEditedPostAttribute( 'template' ) ) {
				resolvedTemplateId = editor.getCurrentTemplateId();
			}
			return {
				postId: editor.getCurrentPostId(),
				postType: editor.getCurrentPostType(),
				templateId: resolvedTemplateId,
				templatePartId:
					'outletproOutletTemplatePartId' in settings
						? settings.outletproOutletTemplatePartId
						: undefined,
				isBlockTheme:
					'outletproIsBlockTheme' in settings &&
					settings.outletproIsBlockTheme === true,
			};
		}, [] );
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
			{ typeof templateId === 'string' &&
				typeof templatePartId === 'string' && (
					<PageEditorCallout>
						<CatalogTemplateLink
							templateId={ templateId }
							templatePartId={ templatePartId }
							currentUrl={ window.location.href }
						/>
					</PageEditorCallout>
				) }
		</CatalogPageDescription>
	);
}
