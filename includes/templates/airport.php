<?php

    if( empty( $airport = airport_by( 'ICAO', $path[1] ?? '' ) ) ) {

        //

    }

    $__site_canonical = 'airport/' . $airport['ICAO'];

    $__site_title = i18n( 'airport-title', $airport['ICAO'], $airport['name'] );

    _header();

    _footer();

?>