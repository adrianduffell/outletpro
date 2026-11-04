/**
 * Copyright 2026 Adrian Duffell
 * Licensed under the GNU General Public License v2.0 or later.
 */

import { __, sprintf } from '@wordpress/i18n';
import {
	InspectorControls,
	useBlockProps,
	RichText,
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { useEntityProp, useEntityRecord } from '@wordpress/core-data';
import { decodeEntities } from '@wordpress/html-entities';
import useUnsignedIntegerEntityProp from '../../use-unsigned-integer-entity-prop';

type Attributes = {
	showOutletLink: boolean;
};

type EditProps = {
	attributes: Attributes;
	setAttributes: ( attributes: Partial< Attributes > ) => void;
};

type OutletPage = {
	link: string;
	title: { rendered: string };
};

type EntityProp< T > = [
	T | undefined,
	( value: T | undefined ) => void,
	unknown,
];

export function Edit( {
	attributes: { showOutletLink },
	setAttributes,
}: EditProps ): JSX.Element {
	const [ message, setMessage ] = useEntityProp(
		'root',
		'site',
		'outletpro_message'
	) as EntityProp< string >;
	const [ outletPageId ] =
		useUnsignedIntegerEntityProp( 'outletpro_page_id' );
	const { record: outletPage } = useEntityRecord< OutletPage >(
		'postType',
		'page',
		outletPageId ?? 0,
		{ enabled: showOutletLink && !! outletPageId }
	);

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'outletpro' ) }>
					<ToggleControl
						label={ __( 'Link to outlet page', 'outletpro' ) }
						checked={ showOutletLink }
						onChange={ ( value ) =>
							setAttributes( { showOutletLink: value } )
						}
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<p { ...blockProps }>
				<RichText
					tagName="span"
					value={ message ?? '' }
					onChange={ ( value: string ) => setMessage( value ) }
					placeholder={ __( 'Write outlet message…', 'outletpro' ) }
				/>
				{ showOutletLink && outletPage && (
					<>
						{ ' ' }
						<a
							href={ outletPage.link }
							onClick={ ( event ) => event.preventDefault() }
						>
							{ sprintf(
								/* translators: %s: Outlet page title in lowercase. */
								__( 'Shop all %s', 'outletpro' ),
								decodeEntities(
									outletPage.title.rendered
								).toLowerCase()
							) }
						</a>
					</>
				) }
			</p>
		</>
	);
}
