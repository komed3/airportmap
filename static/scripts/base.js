var baseurl = window.location.origin,
    resurl = baseurl + '/static/resources/',
    apiurl = baseurl + '/includes/api/',
    use_cookies = false;

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

        if( !!( $.cookie( 'cookie_test' ) || 0 ) ) {

            use_cookies = true;

        }

        $( 'a[target="_blank"]' ).each( function() {

            $( this ).attr( 'rel', 'noopener noreferrer' );

        } );

    } );

    $( document ).on( 'click', '[data-action]', function( e ) {

        prevent( e );

        switch( $( this ).attr( 'data-action' ) ) {

            case 'scroll-to-top':
                $( 'html, body' ).animate( {
                    scrollTop: 0
                }, 'fast' );
                break;

            case 'cookie':
                $.cookie( 'cookie_test', +!!parseInt( $( this ).attr( 'data-cookie' ) ) );
                location.reload();
                break;

            case 'share':
                window.open(
                    {
                        twitter: 'https://twitter.com/intent/tweet?text={TEXT}&url={URL}',
                        facebook: 'https://www.facebook.com/sharer/sharer.php?u={URL}&quote={TEXT}',
                        telegram: 'https://t.me/share/url?url={URL}&text={TEXT}',
                        tumblr: 'https://www.tumblr.com/widgets/share/tool?canonicalUrl={URL}&caption={TEXT}',
                        reddit: 'https://www.reddit.com/submit?url={URL}&title={TEXT}'
                    }[ $( this ).attr( 'data-site' ) ]
                        .replace( '{URL}', $( this ).attr( 'data-url' ) )
                        .replace( '{TEXT}', $( this ).attr( 'data-text' ) ),
                    '_blank'
                );
                break;

            case 'vicinity-my':
                navigator.geolocation.getCurrentPosition( ( position ) => {

                    location.href = baseurl + '/vicinity/' +
                        ( Math.round( position.coords.latitude * 100000 ) / 100000 ) + '/' +
                        ( Math.round( position.coords.longitude * 100000 ) / 100000 );

                } );
                break;

        }

    } );

    $( document ).on( 'change', '[data-action]', function( e ) {

        prevent( e );

        switch( $( this ).attr( 'data-action' ) ) {

            case 'select-language':
                $.cookie( 'locale', $( this ).val().toString().trim() );
                location.reload();
                break;

            case 'select-page':
                location.href = ( $( this ).attr( 'data-base' ) || baseurl + '/' ) +
                    $( this ).val().toString().trim() + '#' + ( $( this ).attr( 'data-jump' ) || '' );
                break;

            case 'select-letter':
                location.href = baseurl + '/list/' + $( this ).val().toString().trim();
                break;

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

            case 'vicinity':
                location.href = baseurl + '/vicinity/' +
                    parseFloat( $( this ).find( '[name="lat"]' ).val() || 0 ) + '/' +
                    parseFloat( $( this ).find( '[name="lon"]' ).val() || 0 );
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