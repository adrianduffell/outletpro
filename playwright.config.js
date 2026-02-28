const { defineConfig } = require( '@playwright/test' );

module.exports = defineConfig( {
	use: {
		// Port 1112 matches the wp-env "port" setting in .wp-env.json.
		baseURL: process.env.WP_BASE_URL || 'http://localhost:1112',
	},
	testDir: './tests/e2e',
} );
