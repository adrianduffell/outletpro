import useStringEntityProp from '../use-string-entity-prop';
import { useEntityProp } from '@wordpress/core-data';

export type Settings = {
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
	scale: number | undefined;
	setScale: ( value: number | undefined ) => void;
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
	const [ fontSize, setFontSize ] = useStringEntityProp(
		'wc_clearance_badge_font_size'
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
	const [ rawScale, setRawScale ] = useEntityProp(
		'root',
		'site',
		'wc_clearance_badge_scale'
	);

	if ( rawScale !== undefined && typeof rawScale !== 'number' ) {
		throw new Error(
			'wc_clearance setting "wc_clearance_badge_scale" must be a number'
		);
	}

	const scale = rawScale;
	const setScale = ( value: number | undefined ) => setRawScale( value );
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
		scale,
		setScale,
		message,
		setMessage,
	};
};

export default useSettings;
