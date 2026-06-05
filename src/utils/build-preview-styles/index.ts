type BuildPreviewStylesParams = {
	label?: string;
	bgColor?: string;
	textColor?: string;
	fontWeight?: string;
	borderColor?: string;
	borderStyle?: string;
	borderWidth?: string;
	borderRadius?: string;
	scale?: number;
	density?: number;
};

export function buildPreviewStyles(
	settings: BuildPreviewStylesParams
): string {
	const entries = {
		'--outletpro-badge-bg-color': settings.bgColor,
		'--outletpro-badge-text-color': settings.textColor,
		'--outletpro-badge-font-weight': settings.fontWeight,
		'--outletpro-badge-border-color': settings.borderColor,
		'--outletpro-badge-border-style': settings.borderStyle,
		'--outletpro-badge-border-width': settings.borderWidth,
		'--outletpro-badge-border-radius': settings.borderRadius,
	};

	const declarations = [
		`--outletpro-badge-label: ${
			settings.label ? JSON.stringify( settings.label ) : 'none'
		}`,
		`--outletpro-badge-scale: ${ settings.scale ?? 'unset' }`,
		`--outletpro-badge-density: ${ settings.density ?? 'unset' }`,
		...Object.entries( entries ).map(
			( [ key, value ] ) => `${ key }: ${ value || 'unset' }`
		),
	].join( '; ' );

	return `:root { ${ declarations }` + ' }';
}
