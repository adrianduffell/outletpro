import { store } from '@wordpress/interactivity';

store( 'wc-outlet/button-interactivity', {
	actions: {
		logHello() {
			// eslint-disable-next-line no-console
			console.log( 'hello' );
		},
	},
} );
