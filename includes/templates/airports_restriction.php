<?php

    if( ( $rest = $DB->query( '
        SELECT  restriction
        FROM    ' . DB_PREFIX . 'airport
        WHERE   restriction = "' . ( $path[2] ?? '' ) . '"
        LIMIT   0, 1
    ' ) )->num_rows != 1 ) {

        __404();

    }

    $rest = $rest->fetch_object()->restriction;
    $name = i18n_save( 'airport-resp-' . $rest ) ?? i18n( 'unknown' );

    $airports = $DB->query( '
        SELECT   *
        FROM     ' . DB_PREFIX . 'airport
        WHERE    restriction = "' . $rest . '"
        ORDER BY tier DESC
    ' )->fetch_all( MYSQLI_ASSOC );

    $position = $DB->query( '
        SELECT  MIN( lat ) AS lat_min,
                MIN( lon ) AS lon_min,
                MAX( lat ) AS lat_max,
                MAX( lon ) AS lon_max,
                AVG( lat ) AS lat_avg,
                AVG( lon ) AS lon_avg
        FROM    ' . DB_PREFIX . 'airport
        WHERE   restriction = "' . $rest . '"
    ' )->fetch_object();

    $count = count( $airports );
    $_count = __number( $count );

    $__site_canonical = $base . 'airports/restriction/' . $rest;

    $__site_title = i18n( 'airports-restriction-title', $name, $rest, $_count );
    $__site_desc = i18n( 'airports-restriction-desc', $name, $rest, $_count );

    add_resource( 'region', 'css', 'region.css' );

    _header();

?>