var maps_config = {},
    maps = {},
    maps_layer = {},
    maps_timeout = {},
    map_sigmet_colors = {
        CONV: '#aa66ff',
        DS: '#eebb55',
        ICE: '#4488dd',
        MTW: '#44aa44',
        SS: '#eebb55',
        TC: '#dd66ee',
        TS: '#ff2200',
        TSGR: '#ff2200',
        TURB: '#ee8844',
        VA: '#bbbbbb'
    };

( function( $ ) {

    var map_set_position = ( uuid ) => {

        let pos = maps[ uuid ].getCenter(),
            zoom = maps[ uuid ].getZoom();

        $.cookie( 'apm_lastpos', JSON.stringify( {
            lat: pos.lat,
            lon: pos.lng,
            zoom: zoom
        } ) );

        location.hash =
            zoom + '/' +
            pos.lat.toFixed(4) + '/' +
            pos.lng.toFixed(4)

    };

    var map_check_zoom = ( uuid ) => {

        let zoom = maps[ uuid ].getZoom();

        $( '#' + uuid + ' [map-action="zoom-in"]' ).prop( 'disabled', false );
        $( '#' + uuid + ' [map-action="zoom-out"]' ).prop( 'disabled', false );
        $( '#' + uuid + ' [map-action="navaids"]' ).hide();

        if( zoom <= ( maps_config[ uuid ].minZoom || 4 ) ) {

            $( '#' + uuid + ' [map-action="zoom-out"]' ).prop( 'disabled', true );

        }

        if( zoom >= ( maps_config[ uuid ].maxZoom || 15 ) ) {

            $( '#' + uuid + ' [map-action="zoom-in"]' ).prop( 'disabled', true );

        }

        if( zoom >= 10 ) {

            $( '#' + uuid + ' [map-action="navaids"]' ).show();

        }

    };

    var map_remove_layer = ( uuid, layer, clear = false ) => {

        maps[ uuid ].removeLayer( maps_layer[ uuid ][ layer ] );

        delete maps_layer[ uuid ][ layer ];

        if( clear ) {

            clearTimeout( maps_timeout[ uuid ][ layer ] );
            clearInterval( maps_timeout[ uuid ][ layer ] );

        }

    };

    var map_load_marker = ( uuid ) => {

        let map = maps[ uuid ],
            bounds = map.getBounds(),
            layer = maps_layer[ uuid ].marker;

        $.ajax( {
            url: apiurl + 'airport_layer.php',
            type: 'post',
            data: {
                token: get_token(),
                bounds: {
                    lat: [ bounds.getNorth(), bounds.getSouth() ],
                    lon: [ bounds.getEast(), bounds.getWest() ]
                },
                navaids: +!!(
                    map.getZoom() >= 10 &&
                    ( $.cookie( 'apm_navaids' ) || 0 ) == 1
                )
            },
            success: ( raw ) => {

                let res = JSON.parse( raw );

                layer.clearLayers();

                Object.values( res.response.navaids ).forEach( ( navaid ) => {

                    layer.addLayer(
                        L.marker( L.latLng(
                            parseFloat( navaid.lat ),
                            parseFloat( navaid.lon )
                        ), {
                            icon: L.divIcon( {
                                iconSize: [ 24, 24 ],
                                iconAnchor: [ 12, 12 ],
                                className: 'navaid-' + navaid.type,
                                html: '<navicon></navicon>'
                            } )
                        } )
                    );

                } );

                Object.values( res.response.airports ).forEach( ( airport ) => {

                    layer.addLayer(
                        L.marker( L.latLng(
                            parseFloat( airport.lat ),
                            parseFloat( airport.lon )
                        ), {
                            icon: L.divIcon( {
                                iconSize: [ 20, 20 ],
                                iconAnchor: [ 10, 10 ],
                                className: 'airport-' + airport.type + ' restriction-' + airport.restriction,
                                html: '<mapicon></mapicon>'
                            } )
                        } )
                    );

                } );

            }
        } );

    };

    var map_sigmets = ( uuid ) => {

        if( 'sigmet' in maps_layer[ uuid ] ) {

            $.cookie( 'apm_sigmet', 0 );

            map_remove_layer( uuid, 'sigmet', true );

        } else {

            $.cookie( 'apm_sigmet', 1 );

            maps_layer[ uuid ].sigmet = L.layerGroup().addTo( maps[ uuid ] );

            map_sigmets_update( uuid );

        }

    };

    var map_sigmets_update = ( uuid ) => {

        if( 'sigmet' in maps_layer[ uuid ] ) {

            let layer = maps_layer[ uuid ].sigmet;

            $.ajax( {
                url: apiurl + 'sigmet_layer.php',
                type: 'post',
                data: {
                    token: get_token()
                },
                success: ( raw ) => {

                    let res = JSON.parse( raw );

                    layer.clearLayers();

                    Object.values( res.response.sigmets ).forEach( function( sigmet ) {

                        JSON.parse( sigmet.polygon ).forEach( function( polygon ) {

                            if( typeof polygon === 'object' && polygon.length > 1 &&
                                polygon.length == polygon.filter( p => typeof p === 'object' ).length ) {

                                layer.addLayer(
                                    L.polygon( polygon.filter( p => p.reverse() ), {
                                        color: map_sigmet_colors[ sigmet.hazard ] || '#eeeeee',
                                        weight: 1,
                                        fillOpacity: 0.25,
                                        dashArray: '4 4'
                                    } )
                                );

                            }

                        } );

                    } );

                    maps_timeout[ uuid ].sigmet = setTimeout( () => {
                        map_sigmets_update( uuid );
                    }, 60000 );

                }
            } );

        }

    };

    var map_day_night_border = ( uuid ) => {

        if( 'terminator' in maps_layer[ uuid ] ) {

            $.cookie( 'apm_day_night', 0 );

            map_remove_layer( uuid, 'terminator', true );

        } else {

            $.cookie( 'apm_day_night', 1 );

            maps_layer[ uuid ].terminator = L.terminator();

            maps_layer[ uuid ].terminator.addTo( maps[ uuid ] );

            maps_layer[ uuid ].terminator.bringToBack();

            map_day_night_update( uuid );

        }

    };

    var map_day_night_update = ( uuid ) => {

        if( 'terminator' in maps_layer[ uuid ] ) {

            maps_layer[ uuid ].terminator.setTime();

            maps_timeout[ uuid ].terminator = setTimeout( () => {
                map_day_night_update( uuid );
            }, 250 );

        }

    };

    $( document ).ready( function() {

        $( '[map-data]' ).each( function() {

            let data = JSON.parse( window.atob( $( this ).attr( 'map-data' ) ) || '{}' ),
                uuid = get_token();

            $( this ).attr( 'id', uuid ).removeAttr( 'map-data' );

            let position = ( pos = $.cookie( 'apm_lastpos' ) || false )
                ? JSON.parse( pos ) : {
                    lat: 40.7,
                    lon: -74,
                    zoom: 6
                };

            maps_config[ uuid ] = data;

            maps_layer[ uuid ] = {};
            maps_timeout[ uuid ] = {};

            maps[ uuid ] = L.map( uuid, {
                center: [
                    position.lat,
                    position.lon
                ],
                zoom: position.zoom,
                maxBounds: L.latLngBounds(
                    L.latLng( -90, -180 ),
                    L.latLng(  90,  180 )
                ),
                maxBoundsViscosity: 1,
                preferCanvas: data.preferCanvas || true,
                scrollWheelZoom: data.wheelZoom || true,
                zoomControl: false
            } );

            L.tileLayer( 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}@2x.png', {
                minZoom: data.minZoom || 4,
                maxZoom: data.maxZoom || 15,
                attribution: '© <a href="https://osm.org">OpenStreetMap</a> contributors © <a href="https://carto.com/attributions">CARTO</a> | ' +
                    'Data by <a href="' + baseurl + '">airportmap.de</a>',
                subdomains: 'abcd'
            } ).addTo( maps[ uuid ] );

            L.control.scale( {
                maxWidth: 140
            } ).addTo( maps[ uuid ] );

            maps[ uuid ].on( 'zoomend', () => {

                map_check_zoom( uuid );

            } );

            map_check_zoom( uuid );

            if( data.save_position || false ) {

                map_set_position( uuid );

                maps[ uuid ].on( 'moveend', () => {

                    map_set_position( uuid );

                } );

            }

            if( ( $.cookie( 'apm_day_night' ) || 0 ) == 1 ) {

                $( '[map-action="day-night"]' ).click();

            }

            if( ( $.cookie( 'apm_sigmet' ) || 0 ) == 1 ) {

                $( '[map-action="sigmet"]' ).click();

            }

            if( ( $.cookie( 'apm_navaids' ) || 0 ) == 1 ) {

                $( '[map-action="navaids"]' ).click();

            }

            maps_layer[ uuid ].marker = L.layerGroup().addTo( maps[ uuid ] );

            maps[ uuid ].on( 'moveend', () => {

                map_load_marker( uuid );

            } );

            map_load_marker( uuid );

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

            case 'navaids':
                $( this ).toggleClass( 'active' );
                $.cookie( 'apm_navaids', +!!$( this ).hasClass( 'active' ) );
                map_load_marker( uuid );
                break;

            case 'sigmet':
                $( this ).toggleClass( 'active' );
                map_sigmets( uuid );
                break;

            case 'day-night':
                $( this ).toggleClass( 'active' );
                map_day_night_border( uuid );
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