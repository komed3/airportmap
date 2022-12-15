var baseurl = window.location.origin,
    locale;

( function( $ ) {

    var setLocale = function() {

        locale = $.cookie( 'locale' ) || 'en-US';

        $( 'html' ).attr( 'lang', locale );

    };

    $( document ).ready( function() {

        setLocale();

    } );

} )( jQuery );