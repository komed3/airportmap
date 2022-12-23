( function( $ ) {

    var baseurl = window.location.origin,
        locale, path, page, __res,
        mypos = {},
        maps = {}, maps_config = {},
        airport_marker = {},
        navaid_marker = {},
        sigmet_marker = {},
        sigmet_colors = {
            CONV: '#aa66ff',
            DS: '#eebb55',
            ICE: '#4488dd',
            MTW: '#88ee88',
            SS: '#eebb55',
            TC: '#dd66ee',
            TS: '#ff2200',
            TSGR: '#ff2200',
            TURB: '#ee8844',
            VA: '#bbbbbb'
        },
        wx_table = {
            // intensity
            '+': 'heavy',
            '-': 'light',
            RE: 'recent',
            VC: 'in the vicinity',
            // characteristic
            BC: 'patches of',
            DR: 'low drifting',
            MI: 'shallow',
            PR: 'partial',
            BL: 'blowing',
            FZ: 'freezing',
            SH: 'showers',
            TS: 'thunderstorms',
            // types
            BR: 'mist',
            DS: 'dust storm',
            DU: 'widespread dust',
            DZ: 'drizzle',
            FC: 'funnel cloud',
            FG: 'fog',
            FU: 'smoke',
            GR: 'hail',
            GS: 'small hail',
            HZ: 'haze',
            IC: 'ice crystals',
            PE: 'ice pellets',
            PO: 'sand whirls',
            PY: 'spray',
            RA: 'rain',
            SA: 'sand',
            SG: 'snow grains',
            SN: 'snow',
            SQ: 'squalls',
            SS: 'sand storm',
            UP: 'unknown',
            VA: 'volcanic ash'
        },
        wx_icons = {
            'thunderstorm': 'thunderstorm',
            'heavy storm': 'cyclone',
            'squalls': 'cyclone',
            'storm': 'storm',
            'sand': 'storm',
            'ice': 'ac_unit',
            'ash': 'lens_blur',
            'fog': 'foggy',
            'haze': 'foggy',
            'dust': 'waves',
            'smoke': 'waves',
            'mist': 'waves',
            'snow': 'weather_snowy',
            'hail': 'grain',
            'grain': 'grain',
            'drizzle': 'rainy',
            'spray': 'rainy',
            'rain': 'rainy',
            'overcast': 'cloud',
            'broken': 'partly_cloudy_day',
            'few': 'partly_cloudy_day',
            'cloud': 'cloud',
            'clear': 'sunny'
        },
        wx_clouds = {
            SKC: 'clear',
            CLR: 'clear',
            CAVOK: 'cavok',
            FEW: 'few clouds',
            SCT: 'scattered clouds',
            BKN: 'broken clouds',
            OVC: 'overcast',
            OVX: 'overcast'
        },
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

        if( navigator.userAgentData.mobile ) {

            _config.max_marker = 50;
            _config.max_label = 25;

        } else {

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

    }

    var setLocale = function() {

        locale = $.cookie( 'locale' ) || 'en-US';

        $( 'html' ).attr( 'lang', locale );

    };

    var loadContent = function() {

        path = window.location.pathname.split( '/' ).filter( n => n );

        switch( ( path[0] || '' ).toString().trim().toLowerCase() ) {

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

            case 'airports':
            case 'world':
                loadPage( 'airports' );
                break;

            case 'continent':
                loadPage( 'airports', {
                    type: 'continent',
                    code: path[1] || ''
                } );
                break;

            case 'country':
                loadPage( 'airports', {
                    type: 'country',
                    code: path[1] || ''
                } );
                break;

            case 'region':
                loadPage( 'airports', {
                    type: 'region',
                    code: path[1] || '',
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

            maps_config[ uuid ] = data;

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

            sigmet_marker[ uuid ] = L.layerGroup().addTo( maps[ uuid ] );
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

            if( data.my || false ) loadMyPos( uuid );

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

        let bounds = map.getBounds(),
            divider = maps_config[ uuid ].divider || 1;

        $.ajax( {
            url: baseurl + '/inc/api/_marker.php',
            type: 'post',
            data: {
                token: getToken(),
                limit: Math.floor( _config.max_marker / divider ),
                zoom: map.getZoom(),
                config: maps_config[ uuid ],
                bounds: {
                    lat: [ bounds.getNorth(), bounds.getSouth() ],
                    lon: [ bounds.getEast(), bounds.getWest() ]
                }
            },
            success: function( response ) {

                let res = JSON.parse( response ),
                    max = Math.floor( _config.max_label / divider );

                sigmet_marker[ uuid ].clearLayers();
                navaid_marker[ uuid ].clearLayers();
                airport_marker[ uuid ].clearLayers();

                Object.values( res.sigmets ).forEach( function( sigmet ) {

                    JSON.parse( sigmet.polygon ).forEach( function( polygon ) {

                        if( typeof polygon === 'object' && polygon.length > 1 &&
                            polygon.length == polygon.filter( p => typeof p === 'object' ).length ) {

                            sigmet_marker[ uuid ].addLayer(
                                L.polygon( polygon.filter( p => p.reverse() ), {
                                    color: sigmet_colors[ sigmet.hazard ] || '#eeeeee',
                                    weight: 1,
                                    fillOpacity: 0.3,
                                    dashArray: '4 4'
                                } )
                            );

                        }

                    } );

                } );

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

        map.setView(
            e.target.getLatLng(),
            Math.max( map.getZoom(), 8 )
        );

    };

    var navaidInfo = function( e, uuid, map ) {

        map.setView(
            e.target.getLatLng(),
            Math.max( map.getZoom(), 8 )
        );

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

    var getCardinalDir = ( hdg = 0 ) => {

        return i18n( [
            'N', 'NNE', 'NE', 'ENE',
            'E', 'ESE', 'SE', 'SSE',
            'S', 'SSW', 'SW', 'WSW',
            'W', 'WNW', 'NW', 'NNW',
            'N'
        ][ Math.round( hdg / 22.5 ) ] )

    }

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
            label: getCardinalDir( heading )
        };

    };

    var wxCloud = ( cloud ) => {

        return cloud in wx_clouds ? wx_clouds[ cloud ] : 'clear';

    };

    var wxIcon = ( text, def = 'sunny' ) => {

        for( const [ search, icon ] of Object.entries( wx_icons ) ) {

            if( text.indexOf( search ) != -1 ) {

                return icon;

            }

        }

        return def;

    };

    var wxConverter = ( wx, vert = 9999, cover = 'CLR' ) => {

        let text = [];

        if( wx.trim().length == 0 ) {

            let def = wxCloud( cover );

            return {
                text: def,
                icon: wxIcon( def )
            }

        }

        wx.trim().split( ' ' ).forEach( symbol => {

            let raw = symbol.match( /^(\+|-|VC|RE)?([A-Z]{2})([A-Z]{2})?$/ ).slice( 1 ),
                parts = raw.map( p => p in wx_table ? wx_table[ p ] : null ).filter( n => n );

            if( parts.includes( 'thunderstorms' ) ||
                parts.includes( 'showers' ) ) {

                parts.pop();

            }

            text.push( parts.join( ' ' ).trim() );

        } );

        text = text.join( ', ' );

        return {
            text: text,
            icon: wxIcon( text )
        }

    };

    var getBreadcrumbs = ( raw, db = 0 ) => {

        let breadcrumbs = [
            '<a href="' + baseurl + '/world">' + i18n( 'World' ) + '</a>'
        ];

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

        $( '[data-nm]' ).each( function() {

            $( this )
                .html( numberFormat( Math.ceil( $( this ).attr( 'data-nm' ) ) ) + '&#8239;nm' )
                .removeAttr( 'data-nm' );

        } );

        $( '[data-mi]' ).each( function() {
            
            let km = $( this ).attr( 'data-mi' ) * 1.609344;

            $( this )
                .html( km > 9.9
                    ? numberFormat( 10 ) + '&#8239;km+'
                    : km > 1
                        ? numberFormat( Math.floor( km ) ) + '&#8239;km'
                        : numberFormat( Math.floor( km * 10 ) * 100 ) + '&#8239;m' )
                .removeAttr( 'data-mi' );

        } );

        $( '[data-temp]' ).each( function() {

            $( this )
                .html( numberFormat( $( this ).attr( 'data-temp' ) ) + '&#8239;°C' )
                .removeAttr( 'data-temp' );

        } );

        $( '[data-kt]' ).each( function() {

            $( this )
                .html( numberFormat( $( this ).attr( 'data-kt' ) ) + '&#8239;kt' )
                .removeAttr( 'data-kt' );

        } );

        $( '[data-altim-hpa]' ).each( function() {

            $( this )
                .html( numberFormat( Math.round( $( this ).attr( 'data-altim-hpa' ) ) ) + '&#8239;hPa' )
                .removeAttr( 'data-altim-hpa' );

        } );

        $( '[data-hdg]' ).each( function() {

            let hdg = parseInt( $( this ).attr( 'data-hdg' ) );

            if( isNaN( hdg ) ) {

                $( this ).empty();

            } else {

                $( this ).removeAttr( 'data-hdg' );
                $( this ).find( '.bug' ).css( 'transform', 'rotate( ' + hdg + 'deg )' );
                $( this ).find( '.deg' ).html( hdg + '°' );
                $( this ).find( '.cardinal' ).html( getCardinalDir( hdg ) );

            }

        } );

        $( '[data-wx]' ).each( function() {

            let wx = wxConverter(
                $( this ).attr( 'data-wx' ),
                $( this ).attr( 'data-vert' ) || 9999,
                $( this ).attr( 'data-cover' ) || 'CLR'
            );

            $( this ).removeAttr( 'data-wx' );
            $( this ).find( '.icon' ).html( wx.icon );
            $( this ).find( '.label' ).html( wx.text );

        } );

        $( '[data-freq]' ).each( function() {

            $( this )
                .html( freqFormat( $( this ).attr( 'data-freq' ) ) )
                .removeAttr( 'data-freq' );

        } );

        $( '[data-localtime]' ).each( function() {

            let date = new Date( Date.parse( $( this ).attr( 'data-localtime' ) ) + (
                ( $( this ).attr( 'data-offset' ) || 0 ) * 60000
            ) );

            $( this )
                .html(
                    date.getDate() + '&nbsp;' + i18n( [
                        'January', 'February', 'March', 'April', 'May', 'June', 'July',
                        'August', 'September', 'October', 'November', 'December'
                    ][ date.getMonth() ] ) + '&nbsp;' +
                    date.getHours().toString().padStart( 2, '0' ) + ':' +
                    date.getMinutes().toString().padStart( 2, '0' )
                )
                .removeAttr( 'data-localtime' );

        } );

        $( '[data-ago]' ).each( function() {

            let diff = Date.now() - Date.parse( $( this ).attr( 'data-ago' ) + 'Z' );

            $( this )
                .addClass( 'ago-' + Math.min( 3, Math.floor( diff / 3600000 ) ) )
                .removeAttr( 'data-ago' )
                .find( '.ago-echo' ).html( diff > 3600000
                    ? Math.floor( diff / 3600000 ) + '&#8239;' + i18n( 'hrs' )
                    : Math.floor( diff / 60000 ) + '&#8239;' + i18n( 'min' )
                );

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

            case 'select-station':
                location.href = baseurl + '/' + (
                    $( this ).attr( 'data-base' ) || ''
                ) + $( this ).val();
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