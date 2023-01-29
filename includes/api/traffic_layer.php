<?php

    require_once __DIR__ . '/api.php';

    $query = '1';

    if( array_key_exists( 'bounds', $_POST ) ) {

        $lat_min = min( $_POST['bounds']['lat'] ?? 0 );
        $lat_max = max( $_POST['bounds']['lat'] ?? 0 );
        $lon_min = min( $_POST['bounds']['lon'] ?? 0 );
        $lon_max = max( $_POST['bounds']['lon'] ?? 0 );

        $query .= ' AND ( lat BETWEEN ' . $lat_min . ' AND ' . $lat_max . ' )' .
                  ' AND ( lon BETWEEN ' . $lon_min . ' AND ' . $lon_max . ' )';

    }

    api_exit( [
        'query' => $query,
        'airports' => $DB->query( '
            SELECT   ICAO, name, lat, lon, alt, type, restriction
            FROM     ' . DB_PREFIX . 'airport
            WHERE    ' . $query . '
            ORDER BY ' . ( $_POST['orderby'] ?? 'tier DESC' ) . '
            LIMIT    0, ' . min( 50, $_POST['limit'] ?? 75 )
        )->fetch_all( MYSQLI_ASSOC ),
        'traffic' => $DB->query( '
            SELECT   ident, callsign, type, lat, lon, alt,
                     hdg, velocity, vrate, ground
            FROM     ' . DB_PREFIX . 'traffic
            WHERE    ' . $query . '
            AND      lat IS NOT NULL
            AND      lon IS NOT NULL
            AND (
                (
                    alt IS NOT NULL AND
                    hdg IS NOT NULL
                ) OR
                ground = 1
            )
            ORDER BY type ASC
            LIMIT    0, ' . min( 200, ( $_POST['limit'] ?? 200 ) * 2 )
        )->fetch_all( MYSQLI_ASSOC )
    ] );

?>