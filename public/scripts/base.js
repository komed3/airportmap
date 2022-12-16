var baseurl = window.location.origin,
    locale, path, page, maps = {};

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

    var loadPage = function( _page, data = {} ) {

        $.ajax( {
            url: baseurl + '/inc/api/' + _page + '.php',
            type: 'post',
            data: data,
            success: function( response ) {

                let res = JSON.parse( response );

                page = res.page;

                document.title = i18n( res.title );

                $( '#content' ).html( res.content );

                dynContent();

                loadMaps();

            }
        } );

    };

    var loadMaps = function() {

        let _maps = $( '[data-map]' );

        $.when(

            ( _maps.length > 0 && (
                typeof L === 'function' || (
                    $( 'head' ).append(
                        '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />'
                    ) && $.ajax( {
                        url: 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.js',
                        dataType: "script",
                        cache: true
                    } )
                )
            ) ),

            $.Deferred( function( deferred ) {
                $( deferred.resolve );
            } )

        ).then( function() {

            _maps.each( function() {

                let uuid = self.crypto.randomUUID();

                $( this ).attr( 'id', uuid );

                maps[ uuid ] = L.map( uuid, {
                    center: [ 51.505, -0.09 ],
                    zoom: 13,
                    preferCanvas: true,
                    scrollWheelZoom: false
                } );

                L.tileLayer( 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: i18n( '© %YEAR% airportmap.de' )
                } ).addTo( maps[ uuid ] );

            } );

        } );

    };

    var i18n = function( text = '' ) {

        return text.replaceAll( '%YEAR%', ( new Date() ).getFullYear() );

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

        $( '[data-href]' ).each( function() {

            $( this )
                .attr( 'href', baseurl + '/' + $( this ).attr( 'data-href' ) )
                .removeAttr( 'data-href' );

        } );

        $( '[data-nav]' ).removeClass( 'active' );
        $( '[data-nav="' + page + '"]' ).addClass( 'active' );

    };

    $( document ).ready( function() {

        setLocale();

        loadContent();

    } );

} )( jQuery );