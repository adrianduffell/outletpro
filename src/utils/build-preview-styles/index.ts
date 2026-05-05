type BuildPreviewStylesParams = {
	label?: string;
	bgColor?: string;
	textColor?: string;
	fontSize?: string;
	fontWeight?: string;
	borderColor?: string;
	borderStyle?: string;
	borderWidth?: string;
	borderRadius?: string;
	paddingTop?: string;
	paddingRight?: string;
	paddingBottom?: string;
	paddingLeft?: string;
};

export function buildPreviewStyles( {
	label,
	bgColor,
	textColor,
	fontSize,
	fontWeight,
	borderColor,
	borderStyle,
	borderWidth,
	borderRadius,
	paddingTop,
	paddingRight,
	paddingBottom,
	paddingLeft,
}: BuildPreviewStylesParams ): string {
	const entries = {
		'--wc-clearance-badge-bg-color': bgColor,
		'--wc-clearance-badge-text-color': textColor,
		'--wc-clearance-badge-font-size': fontSize,
		'--wc-clearance-badge-font-weight': fontWeight,
		'--wc-clearance-badge-border-color': borderColor,
		'--wc-clearance-badge-border-style': borderStyle,
		'--wc-clearance-badge-border-width': borderWidth,
		'--wc-clearance-badge-border-radius': borderRadius,
		'--wc-clearance-badge-padding-top': paddingTop,
		'--wc-clearance-badge-padding-right': paddingRight,
		'--wc-clearance-badge-padding-bottom': paddingBottom,
		'--wc-clearance-badge-padding-left': paddingLeft,
	};

	const styleText = `:root { ${ [
		`--wc-clearance-badge-label: ${ JSON.stringify(
			label ?? ''
		) } !important`,
		...Object.entries( entries ).map(
			( [ key, value ] ) =>
				`${ key }: ${ value ?? 'unset' } !important`
		),
	].join( '; ' ) } }`;

	return styleText;
}
