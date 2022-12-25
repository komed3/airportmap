var maps_config = {},
    maps = {};

( function( $ ) {

    $( document ).ready( function() {

        $( '[map-data]' ).each( function() {

            let data = JSON.parse( window.atob( $( this ).attr( 'map-data' ) ) || '{}' ),
                uuid = get_token();

            $( this ).attr( 'id', uuid ).removeAttr( 'map-data' );

            maps_config[ uuid ] = data;

            maps[ uuid ] = L.map( uuid, {
                center: [ 50, 20 ],
                zoom: data.zoom || 12,
                maxBounds: L.latLngBounds(
                    L.latLng( -90, -180 ),
                    L.latLng(  90,  180 )
                ),
                maxBoundsViscosity: 1,
                preferCanvas: true,
                scrollWheelZoom: data.wheelZoom || true
            } );

            L.tileLayer( 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}@2x.png', {
                minZoom: data.minZoom || 4,
                maxZoom: data.maxZoom || 15,
                attribution: 'Data by <a href="https://osm.org">OSM</a> | © <a href="' + baseurl + '">airportmap.de</a>'
            } ).addTo( maps[ uuid ] );

            L.control.scale().addTo( maps[ uuid ] );

        } );

    } );

} )( jQuery );