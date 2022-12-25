<?php

    function load_tpl_part(
        string $tpl
    ) {

        if( is_readable( TEMPLATE . $tpl . '.php' ) ) {

            include TEMPLATE . $tpl . '.php';

        }

    }

    function _header() {

        load_tpl_part( '_header' );

    }

    function _footer() {

        load_tpl_part( '_footer' );

    }

?>