( function () {
	'use strict';

	var config = window.abpVisitorData || {};

	if ( ! config.ajaxUrl || ! config.logId ) {
		return;
	}

	var started  = Date.now();
	var reported = false;
	var timer    = null;

	function buildPayload( type ) {
		var seconds = Math.round( ( Date.now() - started ) / 1000 );

		return [
			'action=abp_visitor_track',
			'nonce=' + encodeURIComponent( config.nonce ),
			'log_id=' + encodeURIComponent( config.logId ),
			'type=' + encodeURIComponent( type ),
			'duration=' + encodeURIComponent( seconds )
		].join( '&' );
	}

	function send( type ) {
		var body = buildPayload( type );

		if ( window.navigator && navigator.sendBeacon ) {
			try {
				var blob = new Blob( [ body ], { type: 'application/x-www-form-urlencoded;charset=UTF-8' } );

				if ( navigator.sendBeacon( config.ajaxUrl, blob ) ) {
					return;
				}
			} catch ( e ) {}
		}

		try {
			if ( window.fetch ) {
				window.fetch( config.ajaxUrl, {
					method:      'POST',
					body:        body,
					credentials: 'same-origin',
					keepalive:   true
				} );

				return;
			}
		} catch ( e ) {}

		try {
			var image = new Image( 1 );
			image.src = config.ajaxUrl + '?' + body;
		} catch ( e ) {}
	}

	function reportDuration() {
		if ( reported ) {
			return;
		}

		reported = true;
		send( 'duration' );
	}

	document.addEventListener( 'visibilitychange', function () {
		if ( document.visibilityState === 'hidden' ) {
			reportDuration();
		}
	} );

	window.addEventListener( 'pagehide', reportDuration );
	window.addEventListener( 'beforeunload', reportDuration );

	if ( config.heartbeat && parseInt( config.heartbeat, 10 ) > 0 ) {
		timer = window.setInterval( function () {
			if ( document.visibilityState === 'visible' ) {
				send( 'heartbeat' );
			}
		}, parseInt( config.heartbeat, 10 ) * 1000 );
	}

	window.addEventListener( 'unload', function () {
		if ( timer ) {
			window.clearInterval( timer );
		}
	} );
} )();
