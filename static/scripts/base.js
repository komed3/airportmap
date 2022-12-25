var baseurl = window.location.origin,
    mypos = {};

( function( $ ) {

    $( document ).ready( function() {

        navigator.geolocation.getCurrentPosition( function( pos ) {
            mypos = {
                lat: pos.coords.latitude,
                lon: pos.coords.longitude
            };
        } );

        if( 'serviceWorker' in navigator ) {

            navigator.serviceWorker.register( baseurl + '/service-worker.js', { scope: '/' } );

        }

    } );

    $( document ).scroll( function() {

        if( $( window ).scrollTop() > 200 ) {

            $( 'body' ).addClass( 'scroll-to-top' );

        } else {

            $( 'body' ).removeClass( 'scroll-to-top' );

        }

    } );

} )( jQuery );