var maps_config = {},
    maps = {},
    maps_type = {},
    maps_layer = {},
    maps_timeout = {},
    maps_mypos_marker = {},
    map_sigmet_colors = {
        CLD: '#595b64',
        CONV: '#aa66ff',
        DS: '#eebb55',
        FC: '#aa66ff',
        GR: '#ff2200',
        ICE: '#4488dd',
        MTW: '#44aa44',
        SS: '#eebb55',
        TC: '#dd66ee',
        TDO: '#ff2200',
        TS: '#ff2200',
        TSGR: '#ff2200',
        TURB: '#ee8844',
        VA: '#595b64',
        WTSPT: '#4488dd'
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

    var map_halo = ( uuid, position = false ) => {

        if( maps_layer[ uuid ].halo ) {

            maps[ uuid ].removeLayer( maps_layer[ uuid ].halo );

        }

        if( position !== false ) {

            maps_layer[ uuid ].halo = L.marker( position, {
                icon: L.divIcon( {
                    iconSize: [ 72, 72 ],
                    iconAnchor: [ 36, 36 ],
                    className: 'halo',
                    html: '<halo></halo>'
                } )
            } ).addTo( maps[ uuid ] );

        }

    };

    var map_check_zoom = ( uuid ) => {

        let zoom = maps[ uuid ].getZoom();

        $( '#' + uuid ).attr( 'zoom', zoom );

        $( '#' + uuid + ' [map-action="zoom-in"]' ).prop( 'disabled', false );
        $( '#' + uuid + ' [map-action="zoom-out"]' ).prop( 'disabled', false );
        $( '#' + uuid + ' [map-action="navaids"]' ).hide();

        if( zoom <= ( maps_config[ uuid ].minZoom || 4 ) ) {

            $( '#' + uuid + ' [map-action="zoom-out"]' ).prop( 'disabled', true );

        }

        if( zoom >= ( maps_config[ uuid ].maxZoom || 15 ) ) {

            $( '#' + uuid + ' [map-action="zoom-in"]' ).prop( 'disabled', true );

        }

        if( zoom >= 10 && maps_type[ uuid ] == 'airport' ) {

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

        if( 'marker' in maps_timeout[ uuid ] ) {

            clearTimeout( maps_timeout[ uuid ].marker );

        }

        let map = maps[ uuid ],
            bounds = map.getBounds(),
            layer = maps_layer[ uuid ].marker;

        let data = {
            token: get_token(),
            bounds: {
                lat: [ bounds.getNorth(), bounds.getSouth() ],
                lon: [ bounds.getEast(), bounds.getWest() ]
            },
            navaids: +!!(
                map.getZoom() >= 10 && (
                    maps_config[ uuid ].navaids || (
                        $.cookie( 'apm_navaids' ) || 0
                    )
                ) == 1
            )
        };

        if( 'query' in maps_config[ uuid ] ) {

            data = { ...data, ...maps_config[ uuid ].query };

        }

        $.ajax( {
            url: apiurl + maps_type[ uuid ] + '_layer.php',
            type: 'post',
            data: data,
            success: ( raw ) => {

                let res = JSON.parse( raw );

                layer.clearLayers();

                if( 'navaids' in res.response ) {

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
                            } ).bindTooltip(
                                '<div class="IDENT">' + navaid.ident + '</div>' +
                                '<div class="freq">' + navaid.type + ' ' + freq_format( navaid.frequency ) + '</div>', {
                                className: 'tooltip-navaid',
                                direction: 'center',
                                opacity: 1
                            } ).on( 'click', ( e ) => {
                                map_navaid_info( e, uuid, navaid );
                            } )
                        );

                    } );

                }

                if( 'airports' in res.response ) {

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
                            } ).bindTooltip(
                                '<div class="ICAO">' + airport.ICAO + '</div>' +
                                '<div class="name">' + airport.name + '</div>', {
                                className: 'tooltip-airport',
                                direction: 'center',
                                opacity: 1
                            } ).on( 'click', ( e ) => {
                                map_airport_info( e, uuid, airport );
                            } )
                        );

                    } );

                }

                if( 'stations' in res.response ) {

                    Object.values( res.response.stations ).forEach( ( station ) => {

                        layer.addLayer(
                            L.marker( L.latLng(
                                parseFloat( station.lat ),
                                parseFloat( station.lon )
                            ), {
                                icon: L.divIcon( {
                                    iconSize: [ 28, 28 ],
                                    iconAnchor: [ 14, 14 ],
                                    className: 'cat-' + station.cat,
                                    html: '<wxicon></wxicon><windbug style="transform: rotate( ' +
                                        station.wind_dir + 'deg );"></windbug><windflag class="wind-' + (
                                        station.wind_spd == null ? '' : Math.floor( station.wind_spd / 5 ).toString().padStart( 2, '0' )
                                    ) + '" style="transform: rotate( ' + station.wind_dir + 'deg );"></windflag>'
                                } )
                            } ).bindTooltip(
                                '<div class="ICAO">' + station.ICAO + '</div>' +
                                '<div class="name">' + station.name + '</div>', {
                                className: 'tooltip-airport',
                                direction: 'center',
                                opacity: 1
                            } ).on( 'click', ( e ) => {
                                map_airport_info( e, uuid, station );
                            } )
                        );

                    } );

                }

                if( maps_type[ uuid ] == 'weather' ) {

                    maps_timeout[ uuid ].marker = setTimeout( function() {
                        map_load_marker( uuid );
                    }, 300000 );

                }

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

                                let hazard_color = map_sigmet_colors[ sigmet.hazard ] || '#1b1d23';

                                let poly = L.polygon( polygon.filter( p => p.reverse() ), {
                                    color: hazard_color,
                                    weight: 1,
                                    fillOpacity: 0.15,
                                    dashArray: '4 4'
                                } );

                                poly.bindTooltip(
                                    '<div class="hazard" style="color: ' + hazard_color + ';">' + sigmet.hazard + '</div>', {
                                    className: 'tooltip-sigmet',
                                    direction: 'center',
                                    opacity: 1,
                                    permanent: true
                                } );

                                poly.on( 'click', ( e ) => {
                                    map_sigmet_info( poly, e, uuid, sigmet );
                                } );

                                layer.addLayer( poly );

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

    var map_info = ( uuid, infobox, classes = '' ) => {

        let box = $( '#' + uuid + ' .map-infobox' );

        if( 'classes' in infobox ) {

            classes += ' ' + infobox.classes;

        }

        if( 'image' in infobox && infobox.image !== null ) {

            classes += ' image';

            box.find( '.infobox-image' ).css( 'backgroundImage', 'url( ' + infobox.image.file + ' )' ).show();
            box.find( '.infobox-image-credits' ).html( infobox.image.credits );

        } else {

            box.find( '.infobox-image' ).hide();

        }

        box.find( '.infobox-title' ).html( infobox.title );
        box.find( '.infobox-subtitle' ).html( infobox.subtitle );
        box.find( '.infobox-content' ).html( infobox.content );

        if( 'link' in infobox ) {

            box.find( '.infobox-link' ).attr( 'href', infobox.link ).show();
            box.find( '.infobox-linktext' ).html( infobox.linktext );

        } else {

            box.find( '.infobox-link' ).hide();

        }

        box.attr( 'class', 'map-infobox ' + classes ).show();

    };

    var map_airport_info = ( _e, uuid, airport ) => {

        $.ajax( {
            url: apiurl + 'airport_info.php',
            type: 'post',
            data: {
                token: get_token(),
                locale: $.cookie( 'locale' ),
                airport: airport.ICAO
            },
            success: ( raw ) => {

                let res = JSON.parse( raw );

                if( 'infobox' in res.response && typeof res.response.infobox == 'object' ) {

                    let position = L.latLng( airport.lat, airport.lon );

                    maps[ uuid ].flyTo(
                        position,
                        Math.max( 8, maps[ uuid ].getZoom() )
                    );

                    map_halo( uuid, position );

                    map_info( uuid, res.response.infobox, 'airport' );

                }

            }
        } );

    };

    var map_navaid_info = ( _e, uuid, navaid ) => {

        $.ajax( {
            url: apiurl + 'navaid_info.php',
            type: 'post',
            data: {
                token: get_token(),
                locale: $.cookie( 'locale' ),
                navaid: navaid._id
            },
            success: ( raw ) => {

                let res = JSON.parse( raw );

                if( 'infobox' in res.response && typeof res.response.infobox == 'object' ) {

                    let position = L.latLng( navaid.lat, navaid.lon );

                    maps[ uuid ].flyTo(
                        position,
                        Math.max( 10, maps[ uuid ].getZoom() )
                    );

                    map_halo( uuid, position );

                    map_info( uuid, res.response.infobox, 'navaid navaid-' + navaid.type );

                }

            }
        } );

    };

    var map_sigmet_info = ( poly, _e, uuid, sigmet ) => {

        $.ajax( {
            url: apiurl + 'sigmet_info.php',
            type: 'post',
            data: {
                token: get_token(),
                locale: $.cookie( 'locale' ),
                sigmet: sigmet._id
            },
            success: ( raw ) => {

                let res = JSON.parse( raw );

                if( 'infobox' in res.response && typeof res.response.infobox == 'object' ) {

                    maps[ uuid ].fitBounds( poly.getBounds(), {
                        maxZoom: maps_config[ uuid ].maxZoom || 15,
                        padding: [ 50, 50 ]
                    } );

                    map_info( uuid, res.response.infobox, 'sigmet-' + sigmet.hazard );

                }

            }
        } );

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
                uuid = get_token(),
                position = {};

            if( 'position' in data ) {

                position = data.position;

            } else {
                
                position = ( pos = $.cookie( 'apm_lastpos' ) || false )
                    ? JSON.parse( pos ) : {
                        lat: 40.7,
                        lon: -74,
                        zoom: 6
                    };

            }

            $( this ).attr( 'id', uuid ).removeAttr( 'map-data' );

            maps_config[ uuid ] = data;

            maps_layer[ uuid ] = {};
            maps_timeout[ uuid ] = {};

            maps_type[ uuid ] = data.type || ( $.cookie( 'apm_map_type' ) || 'airport' );

            maps[ uuid ] = L.map( uuid, {
                center: [
                    position.lat || 0,
                    position.lon || 0
                ],
                zoom: position.zoom || 6,
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

            maps_layer[ uuid ].marker = L.layerGroup().addTo( maps[ uuid ] );

            if( !( 'supress_day_night' in data ) && ( $.cookie( 'apm_day_night' ) || 0 ) == 1 ) {

                $( '[map-action="day-night"]' ).click();

            }

            if( !( 'supress_sigmets' in data ) && ( $.cookie( 'apm_sigmet' ) || 0 ) == 1 ) {

                $( '[map-action="sigmet"]' ).click();

            }

            if( ( $.cookie( 'apm_navaids' ) || 0 ) == 1 ) {

                $( '[map-action="navaids"]' ).click();

            }

            if( data.save_position || false ) {

                map_set_position( uuid );

                maps[ uuid ].on( 'moveend', () => {

                    map_set_position( uuid );

                } );

            }

            maps[ uuid ].on( 'moveend', () => {

                map_load_marker( uuid );

            } );

            maps[ uuid ].on( 'zoomend', () => {

                map_check_zoom( uuid );

            } );

            if( 'fit_bounds' in data ) {

                maps[ uuid ].fitBounds( data.fit_bounds );

            }

            $( '#' + uuid + ' [map-action="type"][map-type="' + maps_type[ uuid ] + '"]' ).click();

            map_check_zoom( uuid );

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

            case 'type':

                let type = $( this ).attr( 'map-type' );

                $( '[map-action="type"]' ).removeClass( 'active' );
                $( this ).addClass( 'active' );

                if( 'save_type' in maps_config[ uuid ] && maps_config[ uuid ].save_type ) {

                    $.cookie( 'apm_map_type', type );

                }

                maps_type[ uuid ] = type;

                map_check_zoom( uuid );
                map_load_marker( uuid );

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

                    let latlon = new L.LatLng(
                        position.coords.latitude,
                        position.coords.longitude
                    );

                    map.setView( latlon, 8 );

                    if( !maps_mypos_marker[ uuid ] ) {

                        maps_mypos_marker[ uuid ] = true;

                        L.marker( latlon, {
                            icon: L.divIcon( {
                                iconSize: [ 24, 24 ],
                                iconAnchor: [ 12, 12 ],
                                className: 'mypos',
                                html: '<mapicon></mapicon>'
                            } )
                        } ).addTo( map );

                    }

                } );

                break;

            case 'close-infobox':

                $( '#' + uuid + ' .map-infobox' ).attr( 'class', 'map-infobox' ).hide();

                map_halo( uuid );

                break;

            case 'scroll-below':

                $( 'html, body' ).animate( {
                    scrollTop: map._size.y
                }, 'fast' );

                break;

        }

    } );

} )( jQuery );