import useStringEntityProp from '../use-string-entity-prop';
import useUnsignedIntegerEntityProp from '../use-unsigned-integer-entity-prop';

export type Settings = {
	label: string | undefined;
	setLabel: ( value: string | undefined ) => void;
	textColor: string | undefined;
	setTextColor: ( value: string | undefined ) => void;
	bgColor: string | undefined;
	setBgColor: ( value: string | undefined ) => void;
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
	scale: number | undefined;
	setScale: ( value: number | undefined ) => void;
	density: number | undefined;
	setDensity: ( value: number | undefined ) => void;
	message: string | undefined;
	setMessage: ( value: string | undefined ) => void;
};

const useSettings = (): Settings => {
	const [ label, setLabel ] = useStringEntityProp(
		'wc_clearance_badge_label'
	);
	const [ textColor, setTextColor ] = useStringEntityProp(
		'wc_clearance_badge_text_color'
	);
	const [ bgColor, setBgColor ] = useStringEntityProp(
		'wc_clearance_badge_bg_color'
	);
	const [ fontWeight, setFontWeight ] = useStringEntityProp(
		'wc_clearance_badge_font_weight'
	);
	const [ borderColor, setBorderColor ] = useStringEntityProp(
		'wc_clearance_badge_border_color'
	);
	const [ borderStyle, setBorderStyle ] = useStringEntityProp(
		'wc_clearance_badge_border_style'
	);
	const [ borderWidth, setBorderWidth ] = useStringEntityProp(
		'wc_clearance_badge_border_width'
	);
	const [ borderRadius, setBorderRadius ] = useStringEntityProp(
		'wc_clearance_badge_border_radius'
	);
	const [ paddingTop, setPaddingTop ] = useStringEntityProp(
		'wc_clearance_badge_padding_top'
	);
	const [ paddingRight, setPaddingRight ] = useStringEntityProp(
		'wc_clearance_badge_padding_right'
	);
	const [ paddingBottom, setPaddingBottom ] = useStringEntityProp(
		'wc_clearance_badge_padding_bottom'
	);
	const [ paddingLeft, setPaddingLeft ] = useStringEntityProp(
		'wc_clearance_badge_padding_left'
	);
	const [ scale, setScale ] = useUnsignedIntegerEntityProp(
		'wc_clearance_badge_scale'
	);
	const [ density, setDensity ] = useUnsignedIntegerEntityProp(
		'wc_clearance_badge_density'
	);
	const [ message, setMessage ] = useStringEntityProp(
		'wc_clearance_message'
	);

	return {
		label,
		setLabel,
		textColor,
		setTextColor,
		bgColor,
		setBgColor,
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
		scale,
		setScale,
		density,
		setDensity,
		message,
		setMessage,
	};
};

export default useSettings;
