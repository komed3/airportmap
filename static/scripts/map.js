var maps_config = {},
    maps = {},
    maps_layer = {};

( function( $ ) {

    var day_night_border = ( uuid ) => {

        maps_layer[ uuid ].terminator = L.terminator();

        maps_layer[ uuid ].terminator.addTo( maps[ uuid ] );

        setInterval( () => {
            day_night_update( maps_layer[ uuid ].terminator );
        }, 250 );

    };

    var day_night_update = ( t ) => {

        t.setTime();

    };

    $( document ).ready( function() {

        $( '[map-data]' ).each( function() {

            let data = JSON.parse( window.atob( $( this ).attr( 'map-data' ) ) || '{}' ),
                uuid = get_token();

            $( this ).attr( 'id', uuid ).removeAttr( 'map-data' );

            maps_config[ uuid ] = data;

            maps_layer[ uuid ] = {};

            maps[ uuid ] = L.map( uuid, {
                center: [ 40.7, -74 ],
                zoom: data.zoom || 6,
                maxBounds: L.latLngBounds(
                    L.latLng( -90, -180 ),
                    L.latLng(  90,  180 )
                ),
                maxBoundsViscosity: 1,
                preferCanvas: data.preferCanvas || true,
                scrollWheelZoom: data.wheelZoom || true,
                zoomControl: false
            } );

            L.tileLayer( 'https://{s}.basemaps.cartocdn.com/' + ( data.mapStyle || 'light_all' ) + '/{z}/{x}/{y}@2x.png', {
                minZoom: data.minZoom || 4,
                maxZoom: data.maxZoom || 15,
                attribution: 'Data by <a href="https://osm.org">OSM</a> | © <a href="' + baseurl + '">airportmap.de</a>'
            } ).addTo( maps[ uuid ] );

            L.control.scale( {
                maxWidth: 140
            } ).addTo( maps[ uuid ] );

            day_night_border( uuid );

        } );

    } );

    $( document ).on( 'click', '[map-action]', function( e ) {

        prevent( e );

        let uuid = $( this ).closest( '.map' ).attr( 'id' ),
            map = maps[ uuid ];

        switch( ( $( this ).attr( 'map-action' ) || '' ).trim().toLowerCase() ) {

            case 'zoom-in':
                map.zoomIn();
                break;

            case 'zoom-out':
                map.zoomOut();
                break;

            case 'mypos':
                navigator.geolocation.getCurrentPosition( ( position ) => {

                    map.setView( new L.LatLng(
                        position.coords.latitude,
                        position.coords.longitude
                    ), 8 );

                } );
                break;

            case 'scroll-below':
                $( 'html, body' ).animate( {
                    scrollTop: map._size.y
                }, 'fast' );
                break;

        }

    } );

} )( jQuery );