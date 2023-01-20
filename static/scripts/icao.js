( function( $ ) {

    $( document ).on( 'click', 'svg [icao]', function() {

        location.href = baseurl + '/airports/ICAO/' + $( this ).attr( 'icao' );

    } );

} )( jQuery );