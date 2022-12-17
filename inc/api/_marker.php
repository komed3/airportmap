<?php

    require_once __DIR__ . '/../apm.php';

    $lat_min = min( $_POST['bounds']['lat'] ?? 0 );
    $lat_max = max( $_POST['bounds']['lat'] ?? 0 );
    $lon_min = min( $_POST['bounds']['lon'] ?? 0 );
    $lon_max = max( $_POST['bounds']['lon'] ?? 0 );

    $zoom_lvl = $_POST['zoom'] ?? 4;

    if( $zoom_lvl >= 2 ) {

        $airport_types[] = 'large';

    }

    if( $zoom_lvl >= 6 ) {

        $airport_types[] = 'medium';

    }

    if( $zoom_lvl >= 9 ) {

        $airport_types[] = 'small';

    }

    if( $zoom_lvl >= 11 ) {

        $airport_types[] = 'heliport';
        $airport_types[] = 'seaplane';
        $airport_types[] = 'balloonport';

    }

    if( $zoom_lvl >= 13 ) {

        $airport_types[] = 'closed';

    }

    echo json_encode( [
        'airports' => $DB->query( '
            SELECT  ICAO, name, type, lat, lon, alt
            FROM    ' . DB_PREFIX . 'airport
            WHERE   ( lat BETWEEN ' . $lat_min . ' AND ' . $lat_max . ' )
            AND     ( lon BETWEEN ' . $lon_min . ' AND ' . $lon_max . ' )
            AND     type IN ( "' . implode( '", "', $airport_types ) . '" )
        ' )->fetch_all( MYSQLI_ASSOC )
    ], JSON_NUMERIC_CHECK );

?>