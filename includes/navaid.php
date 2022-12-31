<?php

    function navaid_ident(
        array $navaid
    ) {

        return '<morse>' . __morse( $navaid['ident'] ) . '</morse>';

    }

    function navaid_level(
        array $navaid
    ) {

        return i18n( 'level-' . $navaid['level'] );

    }

    function navaid_power(
        array $navaid
    ) {

        return i18n( 'power-' . $navaid['power'] );

    }

?>