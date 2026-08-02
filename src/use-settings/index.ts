/**
 * @copyright 2026 Adrian Duffell
 * @license GNU General Public License v2.0 or later
 */

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
	scale: number | undefined;
	setScale: ( value: number | undefined ) => void;
	density: number | undefined;
	setDensity: ( value: number | undefined ) => void;
	message: string | undefined;
	setMessage: ( value: string | undefined ) => void;
};

const useSettings = (): Settings => {
	const [ label, setLabel ] = useStringEntityProp( 'outletpro_badge_label' );
	const [ textColor, setTextColor ] = useStringEntityProp(
		'outletpro_badge_text_color'
	);
	const [ bgColor, setBgColor ] = useStringEntityProp(
		'outletpro_badge_bg_color'
	);
	const [ fontWeight, setFontWeight ] = useStringEntityProp(
		'outletpro_badge_font_weight'
	);
	const [ borderColor, setBorderColor ] = useStringEntityProp(
		'outletpro_badge_border_color'
	);
	const [ borderStyle, setBorderStyle ] = useStringEntityProp(
		'outletpro_badge_border_style'
	);
	const [ borderWidth, setBorderWidth ] = useStringEntityProp(
		'outletpro_badge_border_width'
	);
	const [ borderRadius, setBorderRadius ] = useStringEntityProp(
		'outletpro_badge_border_radius'
	);
	const [ scale, setScale ] = useUnsignedIntegerEntityProp(
		'outletpro_badge_scale'
	);
	const [ density, setDensity ] = useUnsignedIntegerEntityProp(
		'outletpro_badge_density'
	);
	const [ message, setMessage ] = useStringEntityProp( 'outletpro_message' );

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
		scale,
		setScale,
		density,
		setDensity,
		message,
		setMessage,
	};
};

export default useSettings;
