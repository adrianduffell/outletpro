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
};

function useStringSetting(
	key: string
): [ string | undefined, ( value: string | undefined ) => void ] {
	const [ rawValue, setValue ] = useEntityProp( 'root', 'site', key );
	const value: unknown = rawValue;
	if ( value !== undefined && typeof value !== 'string' ) {
		throw new Error(
			`wc_clearance setting "${ key }" must be a string, got ${ typeof value }`
		);
	}
	return [ value, ( v: string | undefined ) => setValue( v ) ];
}

const useSettings = (): Settings => {
	const [ label, setLabel ] = useStringSetting( 'wc_clearance_badge_label' );
	const [ textColor, setTextColor ] = useStringSetting(
		'wc_clearance_badge_text_color'
	);
	const [ bgColor, setBgColor ] = useStringSetting(
		'wc_clearance_badge_bg_color'
	);
	const [ fontSize, setFontSize ] = useStringSetting(
		'wc_clearance_badge_font_size'
	);
	const [ fontWeight, setFontWeight ] = useStringSetting(
		'wc_clearance_badge_font_weight'
	);
	const [ borderColor, setBorderColor ] = useStringSetting(
		'wc_clearance_badge_border_color'
	);
	const [ borderStyle, setBorderStyle ] = useStringSetting(
		'wc_clearance_badge_border_style'
	);
	const [ borderWidth, setBorderWidth ] = useStringSetting(
		'wc_clearance_badge_border_width'
	);
	const [ borderRadius, setBorderRadius ] = useStringSetting(
		'wc_clearance_badge_border_radius'
	);
	const [ paddingTop, setPaddingTop ] = useStringSetting(
		'wc_clearance_badge_padding_top'
	);
	const [ paddingRight, setPaddingRight ] = useStringSetting(
		'wc_clearance_badge_padding_right'
	);
	const [ paddingBottom, setPaddingBottom ] = useStringSetting(
		'wc_clearance_badge_padding_bottom'
	);
	const [ paddingLeft, setPaddingLeft ] = useStringSetting(
		'wc_clearance_badge_padding_left'
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
	};
};

export default useSettings;
