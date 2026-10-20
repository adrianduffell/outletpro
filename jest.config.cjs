const preset = require( '@wordpress/jest-preset-default/jest-preset' );

module.exports = {
	...preset,
	testPathIgnorePatterns: [
		...( preset.testPathIgnorePatterns ?? [] ),
		'\\.vitest\\.test\\.',
	],
	setupFilesAfterEnv: [
		...( preset.setupFilesAfterEnv || [] ),
		'<rootDir>/jest.setup.js',
	],
};
