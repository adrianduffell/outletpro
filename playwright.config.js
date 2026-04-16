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
	globalSetup: './tests/e2e/global-setup.js',
} );
export default config;
