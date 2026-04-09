import { createElement } from '@wordpress/element';

const ClearanceIcon = createElement(
	'svg',
	{
		viewBox: '0 0 24 24',
		xmlns: 'http://www.w3.org/2000/svg',
		fill: 'none',
	},
	createElement( 'path', {
		d: 'M3 5.5H6L10 11.5L6 17.5H3L7 11.5L3 5.5Z',
		fill: 'none',
		stroke: 'currentColor',
		strokeWidth: 1.1,
		strokeLinejoin: 'miter',
	} ),
	createElement( 'path', {
		d: 'M9 5.5H12L16 11.5L12 17.5H9L13 11.5L9 5.5Z',
		fill: 'none',
		stroke: 'currentColor',
		strokeWidth: 1.1,
		strokeLinejoin: 'miter',
	} ),
	createElement( 'path', {
		d: 'M15 5.5H18L22 11.5L18 17.5H15L19 11.5L15 5.5Z',
		fill: 'none',
		stroke: 'currentColor',
		strokeWidth: 1.1,
		strokeLinejoin: 'miter',
	} )
);

export default ClearanceIcon;
