( function( $ ) {

    var baseurl = window.location.origin,
        locale, path, page, __res,
        mypos = {},
        maps = {},
        airport_marker = {},
        navaid_marker = {},
        _config = {};

    Math.toRad = ( deg ) => {

        return deg * Math.PI / 180;

    };
    
    Math.toDeg = ( rad ) => {

        return rad * 180 / Math.PI;

    };

    var morse = ( input = '', decode = false ) => {

        let alphabet = {
            a: '.-', b: '-...', c: '-.-.', d: '-..',
            e: '.', f: '..-.', g: '--.', h: '....',
            i: '..', j: '.---', k: '-.-', l: '.-..',
            m: '--', n: '-.', o: '---', p: '.--.',
            q: '--.-', r: '.-.', s: '...', t: '-',
            u: '..-', v: '...-', w: '.--', x: '-..-',
            y: '-.--', z: '--..', 1: '.----', 2: '..---',
            3: '...--', 4: '....-', 5: '.....', 6: '-....',
            7: '--...', 8: '---..', 9: '----.', 0: '-----'
        };

        return decode ? input.split( ' ' ).map(
            code => Object.keys( alphabet ).find(
                letter => alphabet[ letter ] === code
            ).toUpperCase()
        ).join( '' ) : input.split( '' ).map(
            letter => alphabet[ letter.toLowerCase() ]
        ).join( ' ' ).replaceAll( '.', '·' );

    };

    var getToken = () => {

        return self.crypto.randomUUID();

    };

    var prevent = ( e ) => {

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

    };

    var checkHardware = () => {

        let cores = navigator.hardwareConcurrency || 4,
            memory = navigator.deviceMemory || 2;

        if( cores < 2 || memory < 1 ) {

            _config.max_marker = 100;
            _config.max_label = 50;

        } else if( cores < 4 || memory < 2 ) {

            _config.max_marker = 150;
            _config.max_label = 75;

        } else {

            _config.max_marker = 250;
            _config.max_label = 100;

        }

    }

    var setLocale = function() {

        locale = $.cookie( 'locale' ) || 'en-US';

        $( 'html' ).attr( 'lang', locale );

    };

    var loadContent = function() {

        path = window.location.pathname.split( '/' ).filter( n => n );

        switch( path[0] ) {

            case 'airport':
                loadPage( 'airport', {
                    airport: path[1] || '',
                    tab: path[2] || 'info',
                    subtab: path[3] || ''
                } );
                break;

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
        _data.path = path;
        _data.referrer = document.referrer;

        $.ajax( {
            url: baseurl + '/inc/api/' + _page + '.php',
            type: 'post',
            data: _data,
            success: function( response ) {

                __res = JSON.parse( response );

                if( 'redirect_to' in __res ) {

                    location.href = baseurl + '/' + __res.redirect_to;

                } else {

                    page = __res.page;

                    document.title = i18n( __res.title );

                    $( '#content' )
                        .attr( 'page', page )
                        .html( __res.content );

                    if( 'searchtext' in __res ) {

                        $( '[data-form="search"] input' ).val( __res.searchtext );

                        document.title += ': ' + __res.searchtext;

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
                parseFloat( mypos.lat ),
                parseFloat( mypos.lon )
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
                limit: _config.max_marker,
                zoom: map.getZoom(),
                bounds: {
                    lat: [ bounds.getNorth(), bounds.getSouth() ],
                    lon: [ bounds.getEast(), bounds.getWest() ]
                }
            },
            success: function( response ) {

                let res = JSON.parse( response ),
                    max = _config.max_label;

                navaid_marker[ uuid ].clearLayers();
                airport_marker[ uuid ].clearLayers();

                Object.values( res.navaids ).forEach( function( navaid ) {

                    navaid_marker[ uuid ].addLayer(
                        L.marker( L.latLng(
                            parseFloat( navaid.lat ),
                            parseFloat( navaid.lon )
                        ), {
                            icon: L.divIcon( {
                                iconSize: '100px',
                                iconAnchor: [ 10, 10 ],
                                className: 'navaid-' + navaid.type,
                                html: '<navicon class="invert"></navicon><div class="info">' +
                                    freqFormat( navaid.frequency ) + '</div>'
                            } )
                        } ).on( 'click', function( e ) {
                            navaidInfo( e, uuid, map );
                        } )
                    );

                } );

                Object.values( res.airports ).forEach( function( airport ) {

                    airport_marker[ uuid ].addLayer(
                        L.marker( L.latLng(
                            parseFloat( airport.lat ),
                            parseFloat( airport.lon )
                        ), {
                            icon: L.divIcon( {
                                iconSize: '100px',
                                iconAnchor: [ 10, 10 ],
                                className: 'type-' + airport.type + ' restrict-' + airport.restriction +
                                    ( --max < 0 ? ' no-label' : '' ),
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

    var i18n = ( text = '' ) => {

        return text.toString().replaceAll( '%YEAR%', ( new Date() ).getFullYear() );

    };

    var numberFormat = ( number, options = {} ) => {

        return ( new Intl.NumberFormat( locale, options ) ).format( number );

    };

    var dateFormat = ( datestring, options = {} ) => {

        let dt = new Date();
        dt.setTime( Date.parse( datestring ) );

        return dt.toLocaleDateString( locale, options );

    };

    var freqFormat = ( frequency ) => {

        frequency = parseInt( frequency );

        return frequency > 1000
            ? numberFormat( frequency / 1000, {
                minimumFractionDigits: 1
            } ) + '&#8239;MHz'
            : numberFormat( frequency ) + '&#8239;kHz';

    };

    var calcDMS = ( decimal, type = 'lat' ) => {

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

    var getHeading = (
        p1_lat, p1_lon,
        p2_lat, p2_lon
    ) => {

        p1_lat = Math.toRad( parseFloat( p1_lat ) );
        p1_lon = Math.toRad( parseFloat( p1_lon ) );
        p2_lat = Math.toRad( parseFloat( p2_lat ) );
        p2_lon = Math.toRad( parseFloat( p2_lon ) );

        let delta_lon = p2_lon - p1_lon;

        let X = Math.cos( p2_lat ) * Math.sin( delta_lon );
        let Y = Math.cos( p1_lat ) * Math.sin( p2_lat ) - Math.sin( p1_lat ) *
                Math.cos( p2_lat ) * Math.cos( delta_lon );

        let heading = ( Math.toDeg( Math.atan2( X, Y ) ) + 360 ) % 360;

        return {
            heading: heading,
            label: i18n( [
                'N', 'NNE', 'NE', 'ENE',
                'E', 'ESE', 'SE', 'SSE',
                'S', 'SSW', 'SW', 'WSW',
                'W', 'WNW', 'NW', 'NNW',
                'N'
            ][ Math.round( heading / 22.5 ) ] )
        };

    };

    var getSlope = ( from, to, length ) => {

        return Math.ceil( Math.abs( ( to - from ) / length * 100 ) ) + '&#8239;%';

    };

    var getBreadcrumbs = ( raw, db = 0 ) => {

        let breadcrumbs = [];

        raw.split( '::' ).forEach( function( part ) {

            if( ( label = part.substring(1) ).length > 0 ) {

                let type = part.charAt(0);

                breadcrumbs.push( '<a href="' + baseurl + '/' + {
                    T: 'continent',
                    C: 'country',
                    R: 'region',
                    M: 'municipality'
                }[ type ] + '/' + label.replaceAll( ' ', '_' ) + '">' +
                    i18n( db && type != 'M' ? $.ajax( {
                        url: baseurl + '/inc/api/_bc.php',
                        type: 'post',
                        data: {
                            token: getToken(),
                            type: type,
                            ident: label
                        },
                        async: false
                    } ).responseText : label ) + '</a>' );

            }

        } );

        return breadcrumbs.join( '<span class="divider">/</span>' );

    };

    var pagination = ( results = 0, page = 1 ) => {

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

        $( '[data-date]' ).each( function() {

            $( this )
                .html( dateFormat( $( this ).attr( 'data-date' ) ) )
                .removeAttr( 'data-date' );

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

        $( '[data-hdg]' ).each( function() {

            let hdg = parseInt( $( this ).attr( 'data-hdg' ) );

            $( this ).removeAttr( 'data-hdg' );
            $( this ).find( '.bug' ).css( 'transform', 'rotate( ' + hdg + 'deg )' );
            $( this ).find( '.deg' ).html( hdg + '°' );

        } );

        $( '[data-slope]' ).each( function() {

            let data = JSON.parse( window.atob( $( this ).attr( 'data-slope' ) ) );

            $( this ).html( getSlope( data.from, data.to, data.length ) ).removeAttr( 'data-slope' );

        } );

        $( '[data-freq]' ).each( function() {

            $( this )
                .html( freqFormat( $( this ).attr( 'data-freq' ) ) )
                .removeAttr( 'data-freq' );

        } );

        $( '[data-nearby]' ).each( function() {

            let data = JSON.parse( window.atob( $( this ).attr( 'data-nearby' ) ) ),
                hdg = getHeading( data.p1.lat, data.p1.lon, data.p2.lat, data.p2.lon );

            $( this ).find( '.heading .bug' ).css( 'transform', 'rotate( ' + hdg.heading + 'deg )' );
            $( this ).find( '.heading .deg' ).html( Math.round( hdg.heading ) + '°' );
            $( this ).find( '.meta .label' ).html( hdg.label );
            $( this ).find( '.meta .dist' ).html( Math.ceil( data.dist ) + '&#8239;nm' );

            $( this ).removeAttr( 'data-nearby' );

        } );

        $( '[data-bc]' ).each( function() {

            $( this )
                .html( getBreadcrumbs( $( this ).attr( 'data-bc' ), $( this ).attr( 'data-bcdb' ) || 0 ) )
                .removeAttr( 'data-bc data-bcdb' );

        } );

        $( '[data-morse]' ).each( function() {

            $( this )
                .html( morse( $( this ).attr( 'data-morse' ), !!( $( this ).attr( 'data-decode' ) || 0 ) ) )
                .removeAttr( 'data-morse data-decode' );

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

        $( '[data-tab]' ).removeClass( 'active' );
        $( '[data-tab="' + ( __res.tab || '_' ) + '"]' ).addClass( 'active' );

        $( '[data-subtab]' ).removeClass( 'active' );
        $( '[data-subtab="' + ( __res.subtab || '_' ) + '"]' ).addClass( 'active' );

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

        if( 'serviceWorker' in navigator ) {

            navigator.serviceWorker.register( baseurl + '/service-worker.min.js', { scope: '/' } );

        }

        checkHardware();

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