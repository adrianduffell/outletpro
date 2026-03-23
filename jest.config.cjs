const preset = require( '@wordpress/jest-preset-default/jest-preset' );

module.exports = {
	...preset,
	moduleNameMapper: {
		...preset.moduleNameMapper,
		'^@wordpress/data$': '<rootDir>/tests/js/__mocks__/@wordpress/data.js',
		'^@wordpress/element$':
			'<rootDir>/tests/js/__mocks__/@wordpress/element.js',
		'^@wordpress/plugins$':
			'<rootDir>/tests/js/__mocks__/@wordpress/plugins.js',
	},
	setupFilesAfterEnv: [
		...( preset.setupFilesAfterEnv || [] ),
		'<rootDir>/jest.setup.js',
	],
};
