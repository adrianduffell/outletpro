const { defineConfig } = require( '@playwright/test' );

module.exports = defineConfig( {
	use: {
		// Port 1111 matches the wp-env "port" setting in .wp-env.json.
		baseURL: process.env.WP_BASE_URL || 'http://localhost:1111',
	},
	testDir: './tests/e2e',
} );
