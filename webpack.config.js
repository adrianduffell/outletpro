import { fileURLToPath } from 'node:url';
import defaultConfig from '@wordpress/scripts/config/webpack.config.js';

export default {
	...defaultConfig,
	entry: () => ( {
		...defaultConfig.entry(),
		'outlet-editor': fileURLToPath(
			new URL( './src/outlet-editor.tsx', import.meta.url )
		),
	} ),
};
