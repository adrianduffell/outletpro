/**
 * Dev build splash screen scripts.
 *
 * @since 1.0.0
 */

( function () {
	const SESSION_KEY = 'outletpro_dev_splash_dismissed';
	const splash = document.getElementById( 'outletpro-dev-splash' );

	if ( ! splash ) {
		return;
	}

	const dismissButton = document.getElementById(
		'outletpro-dev-splash-dismiss'
	);
	const toolbarLink = document.querySelector(
		'#wp-admin-bar-outletpro-dev > a'
	);

	function showSplash() {
		splash.hidden = false;
	}

	function dismissSplash() {
		splash.hidden = true;
		try {
			window.sessionStorage.setItem( SESSION_KEY, '1' );
		} catch ( e ) {}
	}

	if ( ! window.sessionStorage.getItem( SESSION_KEY ) ) {
		showSplash();
	}

	if ( dismissButton ) {
		dismissButton.addEventListener( 'click', dismissSplash );
	}

	if ( toolbarLink ) {
		toolbarLink.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			showSplash();
		} );
	}
} )();
