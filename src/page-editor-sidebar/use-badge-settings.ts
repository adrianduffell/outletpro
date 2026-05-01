import { useEntityProp } from '@wordpress/core-data';

type EntityProp< T > = [
	T | undefined,
	( value: T | undefined ) => void,
	unknown,
];

export type BadgeSettings = {
	label: string | undefined;
	setLabel: ( value: string | undefined ) => void;
	textColor: string | undefined;
	setTextColor: ( value: string | undefined ) => void;
	bgColor: string | undefined;
	setBgColor: ( value: string | undefined ) => void;
	fontSize: string | undefined;
	setFontSize: ( value: string | undefined ) => void;
	fontWeight: string | undefined;
	setFontWeight: ( value: string | undefined ) => void;
	borderColor: string | undefined;
	setBorderColor: ( value: string | undefined ) => void;
	borderStyle: string | undefined;
	setBorderStyle: ( value: string | undefined ) => void;
	borderWidth: string | undefined;
	setBorderWidth: ( value: string | undefined ) => void;
	borderRadius: string | undefined;
	setBorderRadius: ( value: string | undefined ) => void;
	paddingTop: string | undefined;
	setPaddingTop: ( value: string | undefined ) => void;
	paddingRight: string | undefined;
	setPaddingRight: ( value: string | undefined ) => void;
	paddingBottom: string | undefined;
	setPaddingBottom: ( value: string | undefined ) => void;
	paddingLeft: string | undefined;
	setPaddingLeft: ( value: string | undefined ) => void;
};

const useBadgeSettings = (): BadgeSettings => {
	const [ label, setLabel ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_label'
	) as EntityProp< string >;

	const [ textColor, setTextColor ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_text_color'
	) as EntityProp< string >;

	const [ bgColor, setBgColor ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_bg_color'
	) as EntityProp< string >;

	const [ fontSize, setFontSize ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_font_size'
	) as EntityProp< string >;

	const [ fontWeight, setFontWeight ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_font_weight'
	) as EntityProp< string >;

	const [ borderColor, setBorderColor ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_border_color'
	) as EntityProp< string >;

	const [ borderStyle, setBorderStyle ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_border_style'
	) as EntityProp< string >;

	const [ borderWidth, setBorderWidth ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_border_width'
	) as EntityProp< string >;

	const [ borderRadius, setBorderRadius ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_border_radius'
	) as EntityProp< string >;

	const [ paddingTop, setPaddingTop ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_padding_top'
	) as EntityProp< string >;

	const [ paddingRight, setPaddingRight ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_padding_right'
	) as EntityProp< string >;

	const [ paddingBottom, setPaddingBottom ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_padding_bottom'
	) as EntityProp< string >;

	const [ paddingLeft, setPaddingLeft ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_padding_left'
	) as EntityProp< string >;

	return {
		label,
		setLabel,
		textColor,
		setTextColor,
		bgColor,
		setBgColor,
		fontSize,
		setFontSize,
		fontWeight,
		setFontWeight,
		borderColor,
		setBorderColor,
		borderStyle,
		setBorderStyle,
		borderWidth,
		setBorderWidth,
		borderRadius,
		setBorderRadius,
		paddingTop,
		setPaddingTop,
		paddingRight,
		setPaddingRight,
		paddingBottom,
		setPaddingBottom,
		paddingLeft,
		setPaddingLeft,
	};
};

export default useBadgeSettings;
