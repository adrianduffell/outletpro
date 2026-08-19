const config = require( '@wordpress/prettier-config' );

module.exports = {
	...config,
	overrides: [
		...( config.overrides ?? [] ),
		{
			files: '**/*.md',
			options: {
				useTabs: false,
				tabWidth: 2,
			},
		},
	],
};
