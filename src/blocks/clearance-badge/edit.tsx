import { useEffect, useRef } from 'react';
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

type EntityProp< T > = [
	T | undefined,
	( value: T | undefined ) => void,
	unknown,
];

type StyleColor = {
	background?: string;
	text?: string;
};

type Attributes = {
	style?: {
		color?: StyleColor;
		[ key: string ]: unknown;
	};
	[ key: string ]: unknown;
};

type EditProps = {
	attributes: Attributes;
	setAttributes: ( attrs: Partial< Attributes > ) => void;
};

export function Edit( { attributes, setAttributes }: EditProps ): JSX.Element {
	const [ label, setLabel ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_label'
	) as EntityProp< string >;

	const [ bgColor, setBgColor ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_bg_color'
	) as EntityProp< string >;

	const [ textColor, setTextColor ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_text_color'
	) as EntityProp< string >;

	// Track whether we have already seeded the block attributes from the global
	// settings. The seeding should happen once, after both entity props have loaded.
	const seeded = useRef( false );

	// Keep refs in sync with the latest prop values so that effects can read
	// current values without needing them in their dependency arrays.
	const attributesRef = useRef( attributes );
	attributesRef.current = attributes;
	const setAttributesRef = useRef( setAttributes );
	setAttributesRef.current = setAttributes;
	const bgColorRef = useRef( bgColor );
	bgColorRef.current = bgColor;
	const textColorRef = useRef( textColor );
	textColorRef.current = textColor;
	const setBgColorRef = useRef( setBgColor );
	setBgColorRef.current = setBgColor;
	const setTextColorRef = useRef( setTextColor );
	setTextColorRef.current = setTextColor;

	// Seed block style.color from global settings on first load.
	useEffect( () => {
		if (
			seeded.current ||
			bgColor === undefined ||
			textColor === undefined
		) {
			return;
		}
		seeded.current = true;

		const attrs = attributesRef.current;
		const colorUpdate: StyleColor = {};
		if ( attrs.style?.color?.background === undefined ) {
			colorUpdate.background = bgColor;
		}
		if ( attrs.style?.color?.text === undefined ) {
			colorUpdate.text = textColor;
		}

		if ( Object.keys( colorUpdate ).length > 0 ) {
			setAttributesRef.current( {
				style: {
					...attrs.style,
					color: { ...attrs.style?.color, ...colorUpdate },
				},
			} );
		}
	}, [ bgColor, textColor ] );

	// Sync block color attribute changes back to the global settings.
	const blockBg = attributes.style?.color?.background;
	const blockText = attributes.style?.color?.text;

	useEffect( () => {
		if ( ! seeded.current ) {
			return;
		}
		if ( blockBg !== undefined && blockBg !== bgColorRef.current ) {
			setBgColorRef.current( blockBg );
		}
	}, [ blockBg ] );

	useEffect( () => {
		if ( ! seeded.current ) {
			return;
		}
		if ( blockText !== undefined && blockText !== textColorRef.current ) {
			setTextColorRef.current( blockText );
		}
	}, [ blockText ] );

	const blockProps = useBlockProps( {
		className: 'wc-clearance-badge',
	} );

	return (
		<RichText
			{ ...blockProps }
			tagName="span"
			value={ label || __( 'Clearance', 'wc-clearance' ) }
			onChange={ ( value: string ) => setLabel( value ) }
			allowedFormats={ [] }
			placeholder={ __( 'Label', 'wc-clearance' ) }
		/>
	);
}
