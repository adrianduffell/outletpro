( function() {
	var notice = document.querySelector( '.wc-clearance-onboarding-notice' );
	if ( ! notice ) {
		return;
	}
	notice.addEventListener( 'click', function( event ) {
		if ( ! event.target.closest( '.notice-dismiss' ) ) {
			return;
		}
		var xhr = new XMLHttpRequest();
		xhr.open( 'POST', wcClearanceOnboarding.ajaxUrl );
		xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
		xhr.send(
			'action=wc_clearance_dismiss_onboarding_notice&_wpnonce=' +
			encodeURIComponent( wcClearanceOnboarding.nonce )
		);
	} );
}() );
