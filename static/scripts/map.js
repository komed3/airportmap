var maps_limit = 0,
    maps_config = {},
    maps = {},
    maps_type = {},
    maps_layer = {},
    maps_timeout = {},
    maps_loaded = {},
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

    var checkLimit = () => {

        if( 'userAgentData' in navigator && 'mobile' in navigator.userAgentData ) {

            maps_limit = 50;

        } else if(
            ( navigator.hardwareConcurrency || 4 ) < 2 ||
            ( navigator.deviceMemory || 2 ) < 1
        ) {

            maps_limit = 80;

        } else {

            maps_limit = 140;

        }

    };

    var map_check_size = ( uuid ) => {

        let size = maps[ uuid ].getSize();

        if( size.x < 600 || size.y < 400 ) {

            $( '[uuid="' + uuid + '"]' ).addClass( 'mini-map micro-map' );

        } if( size.x < 900 || size.y < 600 ) {

            $( '[uuid="' + uuid + '"]' ).addClass( 'mini-map' );

        } else {

            $( '[uuid="' + uuid + '"]' ).removeClass( 'mini-map micro-map' );

        }

    };

    var map_set_position = ( uuid ) => {

        let pos = maps[ uuid ].getCenter(),
            zoom = maps[ uuid ].getZoom();

        if( use_cookies ) {

            $.cookie( 'apm_lastpos', JSON.stringify( {
                lat: pos.lat,
                lon: pos.lng,
                zoom: zoom
            } ) );

        }

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

        $( '[uuid="' + uuid + '"] [map-action="zoom-in"]' ).prop( 'disabled', false );
        $( '[uuid="' + uuid + '"] [map-action="zoom-out"]' ).prop( 'disabled', false );
        $( '[uuid="' + uuid + '"] [map-action="navaids"]' ).hide();
        $( '[uuid="' + uuid + '"] [map-action="waypoints"]' ).hide();

        if( zoom <= ( maps_config[ uuid ].minZoom || 4 ) ) {

            $( '[uuid="' + uuid + '"] [map-action="zoom-out"]' ).prop( 'disabled', true );

        }

        if( zoom >= ( maps_config[ uuid ].maxZoom || 15 ) ) {

            $( '[uuid="' + uuid + '"] [map-action="zoom-in"]' ).prop( 'disabled', true );

        }

        if( zoom >= 10 && maps_type[ uuid ] == 'airport' ) {

            $( '[uuid="' + uuid + '"] [map-action="navaids"]' ).show();
            $( '[uuid="' + uuid + '"] [map-action="waypoints"]' ).show();

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
            limit: maps_limit,
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
            ),
            waypoints: +!!(
                map.getZoom() >= 10 && (
                    maps_config[ uuid ].waypoints || (
                        $.cookie( 'apm_waypoints' ) || 0
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

                if( 'waypoints' in res.response ) {

                    Object.values( res.response.waypoints ).forEach( ( waypoint ) => {

                        layer.addLayer(
                            L.marker( L.latLng(
                                parseFloat( waypoint.lat ),
                                parseFloat( waypoint.lon )
                            ), {
                                interactive: false,
                                icon: L.divIcon( {
                                    iconSize: [ 20, 20 ],
                                    iconAnchor: [ 10, 10 ],
                                    className: 'waypoint',
                                    html: '<wpicon></wpicon>'
                                } )
                            } ).bindTooltip(
                                '<div class="IDENT">' + waypoint.ident + '</div>', {
                                className: 'tooltip-waypoint',
                                direction: 'center',
                                permanent: true,
                                opacity: 1
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

                if( 'traffic' in res.response ) {

                    Object.values( res.response.traffic ).forEach( ( tfc ) => {

                        layer.addLayer(
                            L.marker( L.latLng(
                                parseFloat( tfc.lat ),
                                parseFloat( tfc.lon )
                            ), {
                                icon: L.divIcon( {
                                    iconSize: [ 24, 24 ],
                                    iconAnchor: [ 12, 12 ],
                                    className: 'traffic t-' + tfc.type + ( tfc.ground ? ' ground' : '' ),
                                    html: '<tfcicon style="transform: rotate( ' + tfc.hdg + 'deg );"></tfcicon>'
                                } )
                            } ).bindTooltip(
                                '<div class="callsign">' + ( tfc.callsign || 'UNKNOWN' ) + '</div>' +
                                '<div class="info">' +
                                    '<span class="alt">' + ( tfc.alt > 10000
                                        ? 'FL' + Math.floor( tfc.alt / 100 )
                                        : Math.max( 0, Math.round( tfc.alt ) ) + 'ft'
                                    ) + '</span>' +
                                    '<span class="velo">' + Math.round( tfc.velocity ) + 'kn</span>' +
                                    ( tfc.vrate != 0 ? '<span class="vrate">VS' +
                                        ( tfc.vrate < 0 ? '–' : '+' ) +
                                        Math.round( Math.abs( tfc.vrate ) ) + '</span>' : '' ) +
                                '</div>', {
                                className: 'tooltip-traffic',
                                direction: 'center',
                                opacity: 1
                            } ).on( 'click', ( e ) => {
                                map_traffic_info( e, uuid, tfc );
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

        try {

            if( !uuid || !maps_layer[ uuid ] || !maps[ uuid ] ) {
                console.warn( 'Invalid map uuid for sigmets' );
                return;
            }

            let sigmet = 0;

            if( 'sigmet' in maps_layer[ uuid ] ) {

                map_remove_layer( uuid, 'sigmet', true );

            } else {

                try {

                    sigmet = 1;

                    maps_layer[ uuid ].sigmet = L.layerGroup().addTo( maps[ uuid ] );

                    map_sigmets_update( uuid );

                } catch( err ) {
                    console.error( 'Error creating SIGMET layer:', err );
                    sigmet = 0;
                    return;
                }

            }

            if( use_cookies ) {

                try {
                    $.cookie( 'apm_sigmet', sigmet );
                } catch( err ) {
                    console.warn( 'Error setting SIGMET cookie:', err );
                }

            }

        } catch( err ) {
            console.error( 'Unexpected error in map_sigmets:', err );
        }

    };

    var map_sigmets_update = ( uuid ) => {

        try {

            if( !uuid || !maps_layer[ uuid ] || !( 'sigmet' in maps_layer[ uuid ] ) ) {
                return;
            }

            let layer = maps_layer[ uuid ].sigmet;

            if( !layer ) {
                return;
            }

            $.ajax( {
                url: apiurl + 'sigmet_layer.php',
                type: 'post',
                data: {
                    token: get_token()
                },
                success: ( raw ) => {

                    try {

                        if( !raw || typeof raw !== 'string' ) {
                            console.warn( 'Invalid SIGMET response format' );
                            return;
                        }

                        let res = null;
                        try {
                            res = JSON.parse( raw );
                        } catch( parseErr ) {
                            console.warn( 'Failed to parse SIGMET JSON:', parseErr );
                            return;
                        }

                        if( !res || typeof res !== 'object' ) {
                            console.warn( 'Invalid SIGMET response object' );
                            return;
                        }

                        if( !res.response || typeof res.response !== 'object' ) {
                            console.warn( 'Invalid SIGMET response.response' );
                            return;
                        }

                        try {
                            layer.clearLayers();
                        } catch( err ) {
                            console.warn( 'Error clearing SIGMET layers:', err );
                        }

                        let sigmets = res.response.sigmets;
                        
                        if( !sigmets ) {
                            sigmets = {};
                        }

                        if( typeof sigmets !== 'object' || Array.isArray( sigmets ) && sigmets === null ) {
                            console.warn( 'Invalid sigmets structure' );
                            return;
                        }

                        try {

                            Object.values( sigmets ).forEach( function( sigmet ) {

                                if( !sigmet || typeof sigmet !== 'object' ) {
                                    return;
                                }

                                try {

                                    let polygonData = sigmet.polygon;

                                    if( !polygonData ) {
                                        return;
                                    }

                                    let polygonArray = null;

                                    if( typeof polygonData === 'string' ) {
                                        try {
                                            polygonArray = JSON.parse( polygonData );
                                        } catch( parseErr ) {
                                            console.warn( 'Failed to parse polygon data:', parseErr );
                                            return;
                                        }
                                    } else if( Array.isArray( polygonData ) ) {
                                        polygonArray = polygonData;
                                    }

                                    if( !Array.isArray( polygonArray ) || polygonArray === null || polygonArray.length === 0 ) {
                                        return;
                                    }

                                    polygonArray.forEach( function( polygon ) {

                                        try {

                                            // Strict polygon validation
                                            if( !polygon ) {
                                                return;
                                            }

                                            if( !Array.isArray( polygon ) ) {
                                                return;
                                            }

                                            if( polygon.length < 2 ) {
                                                return;
                                            }

                                            // Check all elements are valid coordinate pairs
                                            let validPolygon = true;
                                            for( let i = 0; i < polygon.length; i++ ) {
                                                let coord = polygon[ i ];
                                                if( !coord || !Array.isArray( coord ) || coord.length < 2 ) {
                                                    validPolygon = false;
                                                    break;
                                                }
                                                if( typeof coord[0] !== 'number' || typeof coord[1] !== 'number' ) {
                                                    validPolygon = false;
                                                    break;
                                                }
                                            }

                                            if( !validPolygon ) {
                                                return;
                                            }

                                            let hazard_color = '#1b1d23';
                                            if( sigmet.hazard && typeof sigmet.hazard === 'string' && map_sigmet_colors[ sigmet.hazard ] ) {
                                                hazard_color = map_sigmet_colors[ sigmet.hazard ];
                                            }

                                            // Create reversed copy safely
                                            let reversedPolygon = [];
                                            for( let i = polygon.length - 1; i >= 0; i-- ) {
                                                let coord = polygon[ i ];
                                                if( coord && Array.isArray( coord ) && coord.length >= 2 && typeof coord[0] === 'number' && typeof coord[1] === 'number' ) {
                                                    reversedPolygon.push( [ coord[0], coord[1] ] );
                                                }
                                            }

                                            if( reversedPolygon.length < 2 ) {
                                                return;
                                            }

                                            try {
                                                let poly = L.polygon( reversedPolygon, {
                                                    color: hazard_color,
                                                    weight: 1,
                                                    fillOpacity: 0.15,
                                                    dashArray: '4 4'
                                                } );

                                                if( poly && sigmet.hazard && typeof sigmet.hazard === 'string' ) {
                                                    try {
                                                        poly.bindTooltip(
                                                            '<div class="hazard" style="color: ' + hazard_color + ';">' + sigmet.hazard + '</div>', {
                                                            className: 'tooltip-sigmet',
                                                            direction: 'center',
                                                            opacity: 1,
                                                            permanent: true
                                                        } );
                                                    } catch( tooltipErr ) {
                                                        console.warn( 'Error binding tooltip:', tooltipErr );
                                                    }
                                                }

                                                if( poly ) {
                                                    try {
                                                        poly.on( 'click', ( e ) => {
                                                            try {
                                                                map_sigmet_info( poly, e, uuid, sigmet );
                                                            } catch( clickErr ) {
                                                                console.warn( 'Error in sigmet click handler:', clickErr );
                                                            }
                                                        } );
                                                    } catch( eventErr ) {
                                                        console.warn( 'Error binding click event:', eventErr );
                                                    }
                                                }

                                                if( poly && layer ) {
                                                    try {
                                                        layer.addLayer( poly );
                                                    } catch( addErr ) {
                                                        console.warn( 'Error adding layer:', addErr );
                                                    }
                                                }

                                            } catch( polyErr ) {
                                                console.warn( 'Error creating polygon:', polyErr );
                                            }

                                        } catch( polygonErr ) {
                                            console.warn( 'Error processing polygon:', polygonErr );
                                        }

                                    } );

                                } catch( sigmetErr ) {
                                    console.warn( 'Error processing sigmet item:', sigmetErr );
                                }

                            } );

                        } catch( iterErr ) {
                            console.warn( 'Error iterating sigmets:', iterErr );
                        }

                        // Schedule next update
                        if( maps_timeout[ uuid ] && typeof maps_timeout[ uuid ] === 'object' ) {
                            try {
                                if( maps_timeout[ uuid ].sigmet && typeof maps_timeout[ uuid ].sigmet === 'number' ) {
                                    clearTimeout( maps_timeout[ uuid ].sigmet );
                                }
                                maps_timeout[ uuid ].sigmet = setTimeout( () => {
                                    try {
                                        map_sigmets_update( uuid );
                                    } catch( updateErr ) {
                                        console.error( 'Error in scheduled SIGMET update:', updateErr );
                                    }
                                }, 60000 );
                            } catch( timeoutErr ) {
                                console.warn( 'Error setting timeout:', timeoutErr );
                            }
                        }

                    } catch( err ) {
                        console.error( 'Unexpected error in SIGMET success handler:', err );
                    }

                },
                error: ( xhr, status, err ) => {
                    console.warn( 'SIGMET API error:', status, err );
                    // Reschedule update on error
                    if( maps_timeout[ uuid ] && typeof maps_timeout[ uuid ] === 'object' ) {
                        try {
                            if( maps_timeout[ uuid ].sigmet && typeof maps_timeout[ uuid ].sigmet === 'number' ) {
                                clearTimeout( maps_timeout[ uuid ].sigmet );
                            }
                            maps_timeout[ uuid ].sigmet = setTimeout( () => {
                                try {
                                    map_sigmets_update( uuid );
                                } catch( retryErr ) {
                                    console.error( 'Error in retry SIGMET update:', retryErr );
                                }
                            }, 60000 );
                        } catch( timeoutErr ) {
                            console.warn( 'Error scheduling retry:', timeoutErr );
                        }
                    }
                }
            } );

        } catch( err ) {
            console.error( 'Unexpected error in map_sigmets_update:', err );
        }

    };

    var map_info = ( uuid, infobox, classes = '' ) => {

        let box = $( '[uuid="' + uuid + '"] .map-infobox' );

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

        try {

            if( !poly || !uuid || !sigmet || !sigmet._id ) {
                console.warn( 'Invalid parameters for map_sigmet_info' );
                return;
            }

            $.ajax( {
                url: apiurl + 'sigmet_info.php',
                type: 'post',
                data: {
                    token: get_token(),
                    locale: $.cookie( 'locale' ),
                    sigmet: sigmet._id
                },
                success: ( raw ) => {

                    try {

                        if( !raw || typeof raw !== 'string' ) {
                            throw new Error( 'Invalid response format' );
                        }

                        let res = JSON.parse( raw );

                        if( !res || typeof res !== 'object' || !res.response ) {
                            throw new Error( 'Invalid response structure' );
                        }

                        if( 'infobox' in res.response && typeof res.response.infobox == 'object' ) {

                            try {
                                let bounds = poly.getBounds();
                                if( bounds && maps[ uuid ] ) {
                                    maps[ uuid ].fitBounds( bounds, {
                                        maxZoom: maps_config[ uuid ].maxZoom || 15,
                                        padding: [ 50, 50 ]
                                    } );
                                }
                            } catch( err ) {
                                console.warn( 'Error fitting bounds:', err );
                            }

                            if( maps[ uuid ] ) {
                                map_info( uuid, res.response.infobox, 'sigmet-' + ( sigmet.hazard || '' ) );
                            }

                        }

                    } catch( err ) {
                        console.error( 'Error parsing SIGMET info response:', err );
                    }

                },
                error: ( xhr, status, err ) => {
                    console.warn( 'SIGMET info API error:', status, err );
                }
            } );

        } catch( err ) {
            console.error( 'Unexpected error in map_sigmet_info:', err );
        }

    };

    var map_traffic_info = ( _e, uuid, tfc ) => {

        $.ajax( {
            url: apiurl + 'traffic_info.php',
            type: 'post',
            data: {
                token: get_token(),
                locale: $.cookie( 'locale' ),
                ident: tfc.ident
            },
            success: ( raw ) => {

                let res = JSON.parse( raw );

                if( 'infobox' in res.response && typeof res.response.infobox == 'object' ) {

                    let position = L.latLng( tfc.lat, tfc.lon );

                    maps[ uuid ].flyTo(
                        position,
                        Math.max( 8, maps[ uuid ].getZoom() )
                    );

                    map_halo( uuid, position );

                    map_info( uuid, res.response.infobox, 'traffic' );

                }

            }
        } );

    };

    var map_day_night_border = ( uuid ) => {

        let day_night = 0;

        if( 'terminator' in maps_layer[ uuid ] ) {

            map_remove_layer( uuid, 'terminator', true );

        } else {

            day_night = 1;

            maps_layer[ uuid ].terminator = L.terminator();

            maps_layer[ uuid ].terminator.addTo( maps[ uuid ] );

            maps_layer[ uuid ].terminator.bringToBack();

            map_day_night_update( uuid );

        }

        if( use_cookies ) {

            $.cookie( 'apm_day_night', day_night );

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

        checkLimit();

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
            $( this ).closest( '.map-container' ).attr( 'uuid', uuid );

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

            L.tileLayer( 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
                minZoom: data.minZoom || 4,
                maxZoom: data.maxZoom || 15,
                attribution: 'Tiles © <a href="https://esri.com">Esri</a>, DeLorme, NAVTEQ | ' +
                    'Data by <a href="' + baseurl + '">airportmap.de</a>',
            } ).addTo( maps[ uuid ] );

            L.control.scale( {
                maxWidth: 140
            } ).addTo( maps[ uuid ] );

            maps_layer[ uuid ].marker = L.layerGroup().addTo( maps[ uuid ] );

            if( ( !( 'supress_day_night' in data ) || !data.supress_day_night ) &&
                ( $.cookie( 'apm_day_night' ) || 0 ) == 1 ) {

                $( '[map-action="day-night"]' ).click();

            }

            if( ( !( 'supress_sigmets' in data ) || !data.supress_sigmets ) &&
                ( $.cookie( 'apm_sigmet' ) || 0 ) == 1 ) {

                $( '[map-action="sigmet"]' ).click();

            }

            if( ( !( 'navaids' in data ) || data.navaids ) &&
                ( $.cookie( 'apm_navaids' ) || 0 ) == 1 ) {

                $( '[map-action="navaids"]' ).click();

            }

            if( ( !( 'waypoints' in data ) || data.waypoints ) &&
                ( $.cookie( 'apm_waypoints' ) || 0 ) == 1 ) {

                $( '[map-action="waypoints"]' ).click();

            }

            if( data.save_position || false ) {

                map_set_position( uuid );

                maps[ uuid ].on( 'moveend', () => {

                    map_set_position( uuid );

                } );

            }

            maps[ uuid ].on( 'moveend', () => {

                if( uuid in maps_loaded ) {

                    map_load_marker( uuid );

                }

            } );

            maps[ uuid ].on( 'zoomend', () => {

                map_check_zoom( uuid );

            } );

            if( 'fit_bounds' in data ) {

                maps[ uuid ].fitBounds( data.fit_bounds, {
                    animate: false
                } );

            }

            if( 'marker' in data ) {

                data.marker.forEach( ( marker ) => {

                    L.marker( [ marker.lat, marker.lon ], {
                        icon: L.divIcon( {
                            iconSize: [ 24, 24 ],
                            iconAnchor: [ 12, 12 ],
                            className: 'mypos',
                            html: '<mapicon></mapicon>'
                        } )
                    } ).addTo( maps[ uuid  ] );

                } );

            }

            map_check_size( uuid );

            setTimeout( function() {

                if( [ 'airport', 'weather' ].includes( maps_type[ uuid ] ) ) {

                    $( '[uuid="' + uuid + '"] [map-action="type"][map-type="' + maps_type[ uuid ] + '"]' ).click()

                } else {

                    map_load_marker( uuid );

                }

                map_check_zoom( uuid );

                maps_loaded[ uuid ] = true;

            }, 250 );

        } );

    } );

    $( document ).on( 'click', '[map-action]', function( e ) {

        prevent( e );

        let uuid = $( this ).closest( '.map-container' ).find( '.map' ).attr( 'id' ),
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

                if( use_cookies && 'save_type' in maps_config[ uuid ] &&
                    maps_config[ uuid ].save_type ) {

                    $.cookie( 'apm_map_type', type );

                }

                maps_type[ uuid ] = type;

                map_check_zoom( uuid );
                map_load_marker( uuid );

                break;

            case 'navaids':

                $( this ).toggleClass( 'active' );

                if( use_cookies ) {

                    $.cookie( 'apm_navaids', +!!$( this ).hasClass( 'active' ) );

                }

                map_load_marker( uuid );

                break;

            case 'waypoints':

                $( this ).toggleClass( 'active' );

                if( use_cookies ) {

                    $.cookie( 'apm_waypoints', +!!$( this ).hasClass( 'active' ) );

                }

                map_load_marker( uuid );

                break;

            case 'sigmet':

                try {

                    $( this ).toggleClass( 'active' );

                    map_sigmets( uuid );

                } catch( err ) {
                    console.error( 'Error toggling SIGMET layer:', err );
                    $( this ).removeClass( 'active' );
                }

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

                $( '[uuid="' + uuid + '"] .map-infobox' ).attr( 'class', 'map-infobox' ).hide();

                map_halo( uuid );

                break;

            case 'scroll-below':

                $( 'html, body' ).animate( {
                    scrollTop: map._size.y
                }, 'fast' );

                break;

        }

    } );

    $( window ).resize( function() {

        Object.keys( maps ).forEach( ( uuid ) => {

            map_check_size( uuid );

        } );

    } );

} )( jQuery );