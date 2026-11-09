/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityRecord } from '@wordpress/core-data';
import { decodeEntities } from '@wordpress/html-entities';
import { __, sprintf } from '@wordpress/i18n';
import { create, getTextContent, toHTMLString } from '@wordpress/rich-text';
import useUnsignedIntegerEntityProp from '../../use-unsigned-integer-entity-prop';

type OutletPage = {
	link: string;
	title: { rendered: string };
};

type EditProps = {
	attributes: { text?: string };
	setAttributes: ( attributes: { text?: string } ) => void;
};

export function Edit( {
	attributes: { text },
	setAttributes,
}: EditProps ): JSX.Element {
	const [ outletPageId ] =
		useUnsignedIntegerEntityProp( 'outletpro_page_id' );
	const { record: outletPage } = useEntityRecord< OutletPage >(
		'postType',
		'page',
		outletPageId ?? 0,
		{ enabled: !! outletPageId }
	);
	const blockProps = useBlockProps();
	const defaultText = outletPage
		? sprintf(
				/* translators: %s: Lowercase outlet page title. */
				__( 'Shop all %s', 'outletpro' ),
				decodeEntities( outletPage.title.rendered ).toLowerCase()
			)
		: '';
	const displayText = text?.trim() ? text : defaultText;

	return (
		<RichText
			{ ...blockProps }
			tagName="a"
			role="link"
			href={ outletPage?.link }
			value={ toHTMLString( {
				value: create( { text: displayText } ),
			} ) }
			onChange={ ( value: string ) => {
				const updatedText = getTextContent( create( { html: value } ) );
				setAttributes( {
					text: updatedText.trim() ? updatedText : undefined,
				} );
			} }
			onClick={ ( event: React.MouseEvent< HTMLAnchorElement > ) =>
				event.preventDefault()
			}
			allowedFormats={ [] }
			disableLineBreaks
		/>
	);
}
