var baseurl = window.location.origin,
    locale, path, page,
    mypos = {},
    maps = {},
    airport_marker = {},
    navaid_marker = {};

( function( $ ) {

    var getToken = () => {

        return self.crypto.randomUUID();

    };

    var setLocale = function() {

        locale = $.cookie( 'locale' ) || 'en-US';

        $( 'html' ).attr( 'lang', locale );

    };

    var loadContent = function() {

        path = window.location.pathname.split( '/' ).filter( n => n );

        switch( path[0] ) {

            case 'search':
                loadPage( 'search', {
                    searchtext: path[1]
                } );
                break;

            default:
                loadPage( 'map' );
                break;

        }

    };

    var loadPage = function( _page, _data = {} ) {

        _data.token = getToken();

        $.ajax( {
            url: baseurl + '/inc/api/' + _page + '.php',
            type: 'post',
            data: _data,
            success: function( response ) {

                let res = JSON.parse( response );

                page = res.page;

                document.title = i18n( res.title );

                $( '#content' )
                    .attr( 'page', page )
                    .html( res.content );

                if( 'searchtext' in res ) {

                    $( '[data-form="search"] input' ).val( res.searchtext );

                    document.title += ': ' + res.searchtext;

                }

                dynContent();

                loadMaps();

            }
        } );

    };

    var loadMaps = function() {

        $( '[data-map]' ).each( function() {

            let uuid = getToken(),
                data = JSON.parse( window.atob( $( this ).attr( 'data-map' ) ) ),
                lat = data.lat || ( mypos.lat || 0 ),
                lon = data.lon || ( mypos.lon || 0 );

            $( this ).attr( 'id', uuid ).removeAttr( 'data-map' );

            maps[ uuid ] = L.map( uuid, {
                center: [ lat, lon ],
                zoom: data.zoom || 12,
                maxBounds: L.latLngBounds(
                    L.latLng( -90, -180 ),
                    L.latLng(  90,  180 )
                ),
                maxBoundsViscosity: 1,
                preferCanvas: true,
                scrollWheelZoom: data.wheelZoom || false
            } );

            navaid_marker[ uuid ] = L.layerGroup().addTo( maps[ uuid ] );
            airport_marker[ uuid ] = L.layerGroup().addTo( maps[ uuid ] );

            L.tileLayer( 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                minZoom: data.minZoom || 4,
                maxZoom: data.maxZoom || 15,
                attribution:
                    i18n( 'Data by' ) +
                    ' <a href="https://openstreetmap.org">OSM</a> | ' +
                    i18n( '© %YEAR%' ) +
                    ' <a href="' + baseurl + '">airportmap.de</a>'
            } ).addTo( maps[ uuid ] );

            L.control.scale().addTo( maps[ uuid ] );

            loadMyPos( uuid );

            maps[ uuid ].on( 'moveend', function() {

                loadMarker( uuid, maps[ uuid ] );

            } );

            loadMarker( uuid, maps[ uuid ] );

        } );

    };

    var loadMyPos = function( uuid ) {

        if( 'lat' in mypos && 'lon' in mypos ) {

            L.marker( L.latLng(
                mypos.lat,
                mypos.lon
            ), {
                icon: L.divIcon( {
                    iconSize: '100px',
                    iconAnchor: [ 10, 10 ],
                    className: 'mypos',
                    html: '<div class="pic"></div>'
                } )
            } ).addTo( maps[ uuid ] );

        }

    };

    var loadMarker = function( uuid, map ) {

        let bounds = map.getBounds();

        $.ajax( {
            url: baseurl + '/inc/api/_marker.php',
            type: 'post',
            data: {
                token: getToken(),
                closed: true,
                limit: 500,
                zoom: map.getZoom(),
                bounds: {
                    lat: [ bounds.getNorth(), bounds.getSouth() ],
                    lon: [ bounds.getEast(), bounds.getWest() ]
                }
            },
            success: function( response ) {

                let res = JSON.parse( response );

                navaid_marker[ uuid ].clearLayers();
                airport_marker[ uuid ].clearLayers();

                Object.values( res.navaids ).forEach( function( navaid ) {

                    navaid_marker[ uuid ].addLayer(
                        L.marker( L.latLng(
                            navaid.lat,
                            navaid.lon
                        ), {
                            icon: L.divIcon( {
                                iconSize: '100px',
                                iconAnchor: [ 10, 10 ],
                                className: 'navaid-' + navaid.type,
                                html: '<div class="pic"></div><div class="info">' + (
                                    navaid.frequency > 1000
                                        ? numberFormat( navaid.frequency / 1000, {
                                            minimumFractionDigits: 1
                                        } ) + ' MHz'
                                        : numberFormat( navaid.frequency ) + ' kHz'
                                ) + '</div>'
                            } )
                        } ).on( 'click', function( e ) {
                            navaidInfo( e, uuid, map );
                        } )
                    );

                } );

                Object.values( res.airports ).forEach( function( airport ) {

                    airport_marker[ uuid ].addLayer(
                        L.marker( L.latLng(
                            airport.lat,
                            airport.lon
                        ), {
                            icon: L.divIcon( {
                                iconSize: '100px',
                                iconAnchor: [ 10, 10 ],
                                className: 'type-' + airport.type + ' use-' + airport.usage,
                                html: '<div class="pic"></div><div class="info">' +
                                    airport.ICAO + '</div>'
                            } )
                        } ).on( 'click', function( e ) {
                            airportInfo( e, uuid, map );
                        } )
                    );

                } );

            }
        } );

    };

    var airportInfo = function( e, uuid, map ) {

        map.setView( e.target.getLatLng() );

    };

    var navaidInfo = function( e, uuid, map ) {

        map.setView( e.target.getLatLng() );

    };

    var i18n = function( text = '' ) {

        return text.replaceAll( '%YEAR%', ( new Date() ).getFullYear() );

    };

    var numberFormat = function( number, options = {} ) {

        return ( new Intl.NumberFormat( locale, options ) ).format( number );

    };

    var dynContent = function() {

        $( '[data-i18n]' ).each( function() {

            $( this )
                .html( i18n( $( this ).attr( 'data-i18n' ) ) )
                .removeAttr( 'data-i18n' );

        } );

        $( '[data-title]' ).each( function() {

            $( this )
                .attr( 'title', i18n( $( this ).attr( 'data-title' ) ) )
                .removeAttr( 'data-title' );

        } );

        $( '[data-placeholder]' ).each( function() {

            $( this )
                .attr( 'placeholder', i18n( $( this ).attr( 'data-placeholder' ) ) )
                .removeAttr( 'data-placeholder' );

        } );

        $( '[data-number]' ).each( function() {

            $( this )
                .html( numberFormat( $( this ).attr( 'data-number' ) ) )
                .removeAttr( 'data-number' );

        } );

        $( '[data-href]' ).each( function() {

            $( this )
                .attr( 'href', baseurl + '/' + $( this ).attr( 'data-href' ) )
                .removeAttr( 'data-href' );

        } );

        $( '[data-nav]' ).removeClass( 'active' );
        $( '[data-nav="' + page + '"]' ).addClass( 'active' );

    };

    $( document ).on( 'submit', '[data-form]', function( e ) {

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        switch( $( this ).attr( 'data-form' ) ) {

            case 'search':
                location.href = baseurl + '/search/' +
                    $( this ).find( '[name="searchtext"]' ).val().trim();
                break;

        }

        return false;

    } );

    $( document ).ready( function() {

        navigator.geolocation.getCurrentPosition( function( pos ) {
            mypos = {
                lat: pos.coords.latitude,
                lon: pos.coords.longitude
            };
        } );

        setLocale();

        loadContent();

    } );

} )( jQuery );