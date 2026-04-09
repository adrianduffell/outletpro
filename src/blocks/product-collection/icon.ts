import { createElement } from '@wordpress/element';

const ClearanceIcon = createElement(
	'svg',
	{
		viewBox: '0 0 24 24',
		xmlns: 'http://www.w3.org/2000/svg',
		fill: 'none',
	},
	createElement( 'path', {
		d: 'M3 6H6L10 12L6 18H3L7 12L3 6Z',
		fill: 'none',
		stroke: 'currentColor',
		strokeWidth: 1.1,
		strokeLinejoin: 'miter',
	} ),
	createElement( 'path', {
		d: 'M9 6H12L16 12L12 18H9L13 12L9 6Z',
		fill: 'none',
		stroke: 'currentColor',
		strokeWidth: 1.1,
		strokeLinejoin: 'miter',
	} ),
	createElement( 'path', {
		d: 'M15 6H18L22 12L18 18H15L19 12L15 6Z',
		fill: 'none',
		stroke: 'currentColor',
		strokeWidth: 1.1,
		strokeLinejoin: 'miter',
	} )
);

export default ClearanceIcon;
