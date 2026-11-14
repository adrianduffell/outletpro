/**
 * External dependencies
 */
import { defineConfig } from '@playwright/test';

/**
 * WordPress dependencies
 */
import baseConfig from '@wordpress/scripts/config/playwright.config.js';

const config = defineConfig( {
	...baseConfig,
	testDir: './tests/e2e',
	use: {
		...baseConfig.use,
		screenshot: process.env.SCREENSHOT || baseConfig.use.screenshot,
	},
	projects: baseConfig.projects.map( ( project ) => ( {
		...project,
		use: {
			...project.use,
			deviceScaleFactor: 2,
		},
	} ) ),
} );
export default config;
