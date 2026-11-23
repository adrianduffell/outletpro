import defaultConfig from '@wordpress/scripts/config/webpack.config.js';
import WooCommerceDependencyExtractionWebpackPlugin from '@woocommerce/dependency-extraction-webpack-plugin';

export default {
	...defaultConfig,
	plugins: [
		...defaultConfig.plugins.filter(
			( plugin ) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new WooCommerceDependencyExtractionWebpackPlugin(),
	],
};
