/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { createPortal, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import useUnsignedIntegerEntityProp from '../use-unsigned-integer-entity-prop';
import './style.css';

function PageDescription(): JSX.Element | null {
	const [ mountNode, setMountNode ] = useState< HTMLElement | null >( null );

	useEffect( () => {
		const target = document.createElement( 'div' );
		target.className = 'outletpro-page-description';

		function attach() {
			// WordPress has no public insertion point beneath the sidebar title.
			const header = document.querySelector(
				'.editor-post-summary .editor-post-card-panel__header'
			);
			if ( header === null ) {
				return;
			}
			if ( header.nextElementSibling === target ) {
				return;
			}
			header.after( target );
			setMountNode( target );
		}

		const observer = new MutationObserver( attach );
		observer.observe( document.body, { childList: true, subtree: true } );
		attach();

		return () => {
			observer.disconnect();
			target.remove();
		};
	}, [] );

	if ( mountNode === null ) {
		return null;
	}

	return createPortal(
		<p>{ __( 'Displays the store’s outlet products.', 'outletpro' ) }</p>,
		mountNode
	);
}

export default function OutletPageEditorDescription(): JSX.Element | null {
	const { postId, postType } = useSelect( ( select ) => {
		const editor = select( editorStore );
		return {
			postId: editor.getCurrentPostId(),
			postType: editor.getCurrentPostType(),
		};
	}, [] );
	const [ outletPageId ] =
		useUnsignedIntegerEntityProp( 'outletpro_page_id' );

	if ( postType !== 'page' ) {
		return null;
	}
	if ( outletPageId === undefined ) {
		return null;
	}
	if ( postId !== outletPageId ) {
		return null;
	}
	return <PageDescription />;
}
