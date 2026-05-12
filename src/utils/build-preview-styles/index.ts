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
	scale?: number;
};

export function buildPreviewStyles(
	settings: BuildPreviewStylesParams
): string {
	const entries = {
		'--wc-clearance-badge-bg-color': settings.bgColor,
		'--wc-clearance-badge-text-color': settings.textColor,
		'--wc-clearance-badge-font-size': settings.fontSize,
		'--wc-clearance-badge-font-weight': settings.fontWeight,
		'--wc-clearance-badge-border-color': settings.borderColor,
		'--wc-clearance-badge-border-style': settings.borderStyle,
		'--wc-clearance-badge-border-width': settings.borderWidth,
		'--wc-clearance-badge-border-radius': settings.borderRadius,
		'--wc-clearance-badge-padding-top': settings.paddingTop,
		'--wc-clearance-badge-padding-right': settings.paddingRight,
		'--wc-clearance-badge-padding-bottom': settings.paddingBottom,
		'--wc-clearance-badge-padding-left': settings.paddingLeft,
	};

	const declarations = [
		`--wc-clearance-badge-label: ${
			settings.label ? JSON.stringify( settings.label ) : 'none'
		}`,
		`--wc-clearance-badge-scale: ${ settings.scale ?? 'unset' }`,
		...Object.entries( entries ).map(
			( [ key, value ] ) => `${ key }: ${ value || 'unset' }`
		),
	].join( '; ' );

	return `:root { ${ declarations }` + ' }';
}
