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

	const hasSeededColors = useRef( false );

	const blockBg = attributes.style?.color?.background;
	const blockText = attributes.style?.color?.text;

	useEffect( () => {
		if (
			hasSeededColors.current ||
			bgColor === undefined ||
			textColor === undefined
		) {
			return;
		}

		hasSeededColors.current = true;

		setAttributes( {
			style: {
				...attributes.style,
				color: {
					...attributes.style?.color,
					background: blockBg ?? bgColor,
					text: blockText ?? textColor,
				},
			},
		} );
	}, [
		bgColor,
		textColor,
		blockBg,
		blockText,
		attributes.style,
		setAttributes,
	] );

	useEffect( () => {
		if ( ! hasSeededColors.current ) {
			return;
		}

		if ( blockBg !== undefined && blockBg !== bgColor ) {
			setBgColor( blockBg );
		}

		if ( blockText !== undefined && blockText !== textColor ) {
			setTextColor( blockText );
		}
	}, [ blockBg, blockText, bgColor, textColor, setBgColor, setTextColor ] );

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
