( function( $ ) {

    var __filter = {
        hazard: '',
        change: ''
    };

    $( document ).on( 'change', '.filter select', function() {

        let filter = $( this ).attr( 'filter' ),
            value = $( this ).val();

        __filter[ filter ] = value;

        $( '.sigmets .sigmet' ).each( function() {

            if( (
                __filter.hazard.length > 0 &&
                !$( this ).hasClass( 'hazard-' + __filter.hazard )
            ) || (
                __filter.change.length > 0 &&
                !$( this ).hasClass( 'change-' + __filter.change )
            ) ) {

                $( this ).hide();

            } else {

                $( this ).show();

            }

        } );

    } );

} )( jQuery );