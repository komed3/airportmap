var baseurl = window.location.origin,
    locale, path, page,
    mypos = {}, maps = {};

( function( $ ) {

    var setLocale = function() {

        locale = $.cookie( 'locale' ) || 'en-US';

        $( 'html' ).attr( 'lang', locale );

    };

    var loadContent = function() {

        path = window.location.pathname.split( '/' ).filter( n => n );

        switch( path[0] ) {

            default:
                loadPage( 'map' );
                break;

        }

    };

    var loadPage = function( _page, _data = {} ) {

        _data.token = self.crypto.randomUUID();

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

                dynContent();

                loadMaps();

            }
        } );

    };

    var loadMaps = function() {

        $( '[data-map]' ).each( function() {

            let uuid = self.crypto.randomUUID(),
                data = JSON.parse( window.atob( $( this ).attr( 'data-map' ) ) ),
                lat = data.lat || ( mypos.lat || 0 ),
                lon = data.lon || ( mypos.lon || 0 );

            $( this ).attr( 'id', uuid ).removeAttr( 'data-map' );

            maps[ uuid ] = L.map( uuid, {
                center: [ lat, lon ],
                zoom: data.zoom || 12,
                preferCanvas: true,
                scrollWheelZoom: data.wheelZoom || false
            } );

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

        } );

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
                    $( this ).find( '[name="searchtext"]' ).val();
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