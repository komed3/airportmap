<?php

    $__site_title;

    function _base_url(
        string $url = ''
    ) {

        echo SITE . $url;

    }

    function load_tpl_part(
        string $tpl
    ) {

        if( is_readable( TEMPLATE . $tpl . '.php' ) ) {

            include TEMPLATE . $tpl . '.php';

        }

    }

    function site_header() {

        ?><title><?php _i18n( $__site_title ?? 'site-title-default' ); ?> — Airportmap</title><?php

    }

    function _header() {

        load_tpl_part( '_header' );

    }

    function _footer() {

        load_tpl_part( '_footer' );

    }

?>