import { createElement } from '@wordpress/element';

const OutletIcon = createElement(
	'svg',
	{
		viewBox: '0 0 24 24',
		xmlns: 'http://www.w3.org/2000/svg',
		fill: 'none',
	},
	createElement( 'path', {
		d: 'M6 5.5H9L13 11.5L9 17.5H6L10 11.5L6 5.5Z',
		fill: 'none',
		stroke: 'currentColor',
		strokeWidth: 1.1,
		strokeLinejoin: 'miter',
	} ),
	createElement( 'path', {
		d: 'M12 5.5H15L19 11.5L15 17.5H12L16 11.5L12 5.5Z',
		fill: 'none',
		stroke: 'currentColor',
		strokeWidth: 1.1,
		strokeLinejoin: 'miter',
	} )
);

export default OutletIcon;
