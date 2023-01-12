<?php

    require_once __DIR__ . '/api.php';

    $skip = array_filter( explode( ' ', file_get_contents( './file_search_skip.txt' ) ?? '' ) );

    if( empty( $_GET['search'] ) ) {

        $res = $DB->query( '
            SELECT   ICAO
            FROM     ' . DB_PREFIX . 'airport
            WHERE    ICAO NOT IN (
                SELECT  airport
                FROM    ' . DB_PREFIX . 'image
            )
            AND      ICAO NOT IN (
                "' . implode( '", "', $skip ) . '"
            )
            ORDER BY tier DESC
            LIMIT    0, 1
        ' );

        if( $res->num_rows ) {

            header( 'LOCATION: ' . API . 'file_search.php?api_key=' . API_KEY . '&search=' . $res->fetch_object()->ICAO );

        }

    } else {

        //

    }

?>