var baseurl = window.location.origin,
    resurl = baseurl + '/static/resources/',
    apiurl = baseurl + '/includes/api/';

var get_token = () => {

    return self.crypto.randomUUID();

};

var prevent = ( e ) => {

    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

};

var number_format = ( number, options = {} ) => {

    return ( new Intl.NumberFormat(
        $.cookie( 'locale' ) || 'en',
        options
    ) ).format( number );

}

var freq_format = ( frequency ) => {

    frequency = parseInt( frequency );

    return frequency > 1000
        ? number_format( frequency / 1000, {
            minimumFractionDigits: 1,
            maximumFractionDigits: 3
        } ) + '&#8239;MHz'
        : number_format( frequency ) + '&#8239;kHz';

};

( function( $ ) {

    $( document ).ready( function() {

        $.cookie.defaults = {
            path: '/',
            expires: 365
        };

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

    $( document ).on( 'change', '[data-action]', function( e ) {

        prevent( e );

        switch( $( this ).attr( 'data-action' ) ) {

            case 'select-station':
                location.href = baseurl + '/' + $( this ).attr( 'data-base' ) + $( this ).val() + (
                    ( jump = $( this ).attr( 'data-jump' ) ) ? '#' + jump : ''
                );
                break;

        }

    } );

    $( document ).on( 'submit', '[data-form]', function( e ) {

        prevent( e );

        switch( $( this ).attr( 'data-form' ) ) {

            case 'search':
                location.href = baseurl + '/search/' + encodeURI(
                    ( $( this ).find( '[name="searchtext"]' ).val() || '' ).toString().trim()
                );
                break;

            case 'embed':
                location.href = baseurl + '/embed/' +
                    ( $( this ).find( '[name="code"]' ).val() || '' ).toString().trim().toUpperCase() + '/' +
                    ( $( this ).find( '[name="lang"]' ).val() || '' ).toString().trim().toLowerCase();
                break;

        }

        return false;

    } );

    $( document ).scroll( function() {

        if( $( window ).scrollTop() > 200 ) {

            $( 'body' ).addClass( 'scroll-to-top' );

        } else {

            $( 'body' ).removeClass( 'scroll-to-top' );

        }

    } );

} )( jQuery );