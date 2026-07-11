const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils' );

module.exports = {
	...defaultConfig,
	entry: async () => {
		const defaultEntries = await getWebpackEntryPoints( 'script' )();
		return {
			...defaultEntries,
			'welcome-page': './src/welcome-page/index.tsx',
		};
	},
};
