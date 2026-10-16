const wpPlugin = require( '@wordpress/eslint-plugin' );
const jestPlugin = require( 'eslint-plugin-jest' );

module.exports = [
	{ ignores: [ '**/build/**', '**/node_modules/**', '**/vendor/**' ] },
	{ files: [ '**/*.jsx', '**/*.ts', '**/*.tsx', '**/*.mts', '**/*.cts' ] },
	...wpPlugin.configs.recommended,
	{
		...jestPlugin.configs[ 'flat/recommended' ],
		ignores: [ '**/*.vitest.test.*' ],
		files: [
			'**/@(test|__tests__)/**/*.{js,jsx,ts,tsx,mjs,cjs,mts,cts}',
			'**/*.@(test|spec).{js,jsx,ts,tsx,mjs,cjs,mts,cts}',
		],
	},
	...wpPlugin.configs[ 'test-unit' ].map( ( config ) => ( {
		...config,
		files: [ '**/*.vitest.test.{js,jsx,ts,tsx,mjs,cjs,mts,cts}' ],
	} ) ),
];
