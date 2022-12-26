var baseurl = window.location.origin;

var get_token = () => {

    return self.crypto.randomUUID();

};

var prevent = ( e ) => {

    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

};

( function( $ ) {

    $( document ).ready( function() {

        if( 'serviceWorker' in navigator ) {

            navigator.serviceWorker.register( baseurl + '/service-worker.js', { scope: '/' } );

        }

    } );

    $( document ).on( 'click', '[data-action]', function( e ) {

        prevent( e );

        switch( $( this ).attr( 'data-action' ) ) {

            case 'scroll-to-top':
                $( 'html, body' ).animate( {
                    scrollTop: 0
                }, 'fast' );
                break;

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