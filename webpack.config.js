import { createRequire } from 'module';

const require = createRequire( import.meta.url );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

export default {
	...defaultConfig,
	module: {
		...defaultConfig.module,
		rules: [
			...defaultConfig.module.rules,
			// Disable ESM fullySpecified resolution for .js files in src/ so
			// that directory-style imports resolve correctly when "type":
			// "module" is set in package.json.
			{
				test: /\.js$/,
				include: /\/src\//,
				resolve: { fullySpecified: false },
			},
		],
	},
};
