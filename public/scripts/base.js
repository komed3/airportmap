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

    var prevent = ( e ) => {

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

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
                    searchtext: ( path[1] || '' ).replaceAll( '_', ' ' ),
                    page: parseInt( path[2] || 1 )
                } );
                break;

            default:
                loadPage( 'map' );
                break;

        }

    };

    var loadPage = function( _page, _data = {} ) {

        _data.token = getToken();
        _data.referrer = document.referrer;

        $.ajax( {
            url: baseurl + '/inc/api/' + _page + '.php',
            type: 'post',
            data: _data,
            success: function( response ) {

                let res = JSON.parse( response );

                if( 'redirect_to' in res ) {

                    location.href = baseurl + '/' + res.redirect_to;

                } else {

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
                    html: '<navicon class="invert"></navicon>'
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
                                html: '<navicon class="invert"></navicon><div class="info">' + (
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
                                className: 'type-' + airport.type + ' restrict-' + airport.restriction,
                                html: '<navicon class="invert"></navicon><div class="info">' +
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

    var calcDMS = function( decimal, type = 'lat' ) {

        let dec = parseFloat( decimal ),
            abs = Math.abs( dec ),
            deg = Math.floor( abs ),
            sub = ( abs - deg ) * 60,
            min = Math.floor( sub ),
            sec = Math.floor( ( sub - min ) * 60 );

        return deg + '°&#8239;' + min + '′&#8239;' + sec + '″&#8239;' + {
            lat: dec < 0 ? 'S' : 'N',
            lon: dec < 0 ? 'W' : 'E'
        }[ type ];

    };

    var getBreadcrumbs = function( raw ) {

        let breadcrumbs = [];

        raw.split( '/' ).forEach( function( part ) {

            if( ( label = part.substring(1) ).length > 0 ) {

                breadcrumbs.push( '<a href="' + baseurl + '/' + {
                    T: 'continent',
                    C: 'country',
                    R: 'region',
                    M: 'municipality'
                }[ part.charAt(0) ] + '/' + label.replaceAll( ' ', '_' ) + '">' +
                    i18n( label ) + '</a>' );

            }

        } );

        return breadcrumbs.join( '<span class="divider">/</span>' );

    };

    var pagination = function( results = 0, page = 1 ) {

        if( results <= 6 ) return '';

        let maxpage = Math.ceil( results / 24 ),
            pageurl = baseurl + '/' + path.slice( 0, 2 ).join( '/' ) + '/',
            pagelinks = [],
            latest = 0;

        [
            1,
            Math.max( 1, page - 2 ),
            Math.max( 1, page - 1 ),
            page,
            Math.min( maxpage, page + 1 ),
            Math.min( maxpage, page + 2 ),
            maxpage
        ].filter( ( val, idx, self ) => {
            return self.indexOf( val ) === idx;
        } ).forEach( ( pageno ) => {

            if( pageno > latest + 1 )
                pagelinks.push( '<span class="dots"><span>…</span></span>' );

            pagelinks.push(
                pageno == page
                    ? '<span class="curr"><span>' + numberFormat( pageno ) + '</span></span>'
                    : '<a class="link" href="' + pageurl + pageno + '"><span>' + numberFormat( pageno ) + '</span></a>'
            );

            latest = pageno;

        } );

        if( pagelinks.length <= 1 )
            pagelinks = [];

        return '<div class="pagelinks">' +
            pagelinks.join( '' ) +
        '</div>' +
        '<div class="results">' +
            '<b>' + numberFormat( results ) + '</b>&nbsp;' + i18n( 'Results' ) +
        '</div>';

    }

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

        $( '[data-lat]' ).each( function() {

            $( this )
                .html( calcDMS( $( this ).attr( 'data-lat' ), 'lat' ) )
                .removeAttr( 'data-lat' );

        } );

        $( '[data-lon]' ).each( function() {

            $( this )
                .html( calcDMS( $( this ).attr( 'data-lon' ), 'lon' ) )
                .removeAttr( 'data-lon' );

        } );

        $( '[data-alt]' ).each( function() {

            $( this )
                .html( numberFormat( $( this ).attr( 'data-alt' ) ) +
                       '&#8239;' + i18n( 'ft' ) )
                .removeAttr( 'data-alt' );

        } );

        $( '[data-msl]' ).each( function() {

            $( this )
                .html( numberFormat( Math.ceil( $( this ).attr( 'data-msl' ) / 3.281 ) ) +
                       '&#8239;m&nbsp;' + i18n( 'MSL' ) )
                .removeAttr( 'data-msl' );

        } );

        $( '[data-bc]' ).each( function() {

            $( this )
                .html( getBreadcrumbs( $( this ).attr( 'data-bc' ) ) )
                .removeAttr( 'data-bc' );

        } );

        $( '[data-pagination]' ).each( function() {

            let _data = JSON.parse( window.atob( $( this ).attr( 'data-pagination' ) ) );

            $( this )
                .html( pagination( _data.results, _data.page ) )
                .removeAttr( 'data-pagination' );

        } );

        $( '[data-href]' ).each( function() {

            $( this )
                .attr( 'href', baseurl + '/' + $( this ).attr( 'data-href' ).replaceAll( ' ', '_' ) )
                .removeAttr( 'data-href' );

        } );

        $( '[data-nav]' ).removeClass( 'active' );
        $( '[data-nav="' + page + '"]' ).addClass( 'active' );

        $( '[data-action="select-language"]' ).each( function() {

            $( this ).val( locale );

        } );

    };

    $( document ).on( 'submit', '[data-form]', function( e ) {

        prevent( e );

        switch( $( this ).attr( 'data-form' ) ) {

            case 'search':
                location.href = baseurl + '/search/' +
                    $( this ).find( '[name="searchtext"]' ).val().trim().replaceAll( ' ', '_' );
                break;

        }

        return false;

    } );

    $( document ).on( 'click', '[data-action]', function( e ) {

        prevent( e );

        switch( $( this ).attr( 'data-action' ) ) {

            case 'scroll-to-top':
                $( 'html, body' ).animate( { scrollTop: 0 }, 'fast' );
                break;

        }

    } );

    $( document ).on( 'change', '[data-action]', function( e ) {

        prevent( e );

        switch( $( this ).attr( 'data-action' ) ) {

            case 'select-language':
                $.cookie( 'locale', $( this ).val() );
                location.reload();
                break;

        }

    } );

    $( document ).ready( function() {

        $.cookie.defaults = {
            path: '/',
            expires: 365
        };

        navigator.geolocation.getCurrentPosition( function( pos ) {
            mypos = {
                lat: pos.coords.latitude,
                lon: pos.coords.longitude
            };
        } );

        setLocale();

        loadContent();

    } );

    $( document ).scroll( function() {

        if( $( window ).scrollTop() > 200 ) {

            $( 'body' ).addClass( 'scroll-to-top' );

        } else {

            $( 'body' ).removeClass( 'scroll-to-top' );

        }

    } );

} )( jQuery );