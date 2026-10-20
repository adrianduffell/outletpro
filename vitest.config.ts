import { defineConfig } from 'vitest/config';

export default defineConfig( {
	test: {
		globals: false,
		projects: [
			{
				test: {
					name: 'node',
					environment: 'node',
					include: [ 'src/**/*.vitest.test.{ts,tsx,js,jsx}' ],
					exclude: [ 'src/**/*.jsdom.vitest.test.{ts,tsx,js,jsx}' ],
				},
			},
			{
				test: {
					name: 'jsdom',
					environment: 'jsdom',
					include: [ 'src/**/*.jsdom.vitest.test.{ts,tsx,js,jsx}' ],
					setupFiles: [ './vitest.setup.ts' ],
				},
			},
		],
	},
} );
