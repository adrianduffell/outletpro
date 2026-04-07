const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		index: './src/index.ts',
		'checkout-fill': './src/checkout-fill/index.tsx',
	},
};
