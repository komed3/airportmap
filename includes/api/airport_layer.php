<?php

    require_once __DIR__ . '/api.php';

    $query = $navaids_query = '1';

    if( array_key_exists( 'bounds', $_POST ) ) {

        $lat_min = min( $_POST['bounds']['lat'] ?? 0 );
        $lat_max = max( $_POST['bounds']['lat'] ?? 0 );
        $lon_min = min( $_POST['bounds']['lon'] ?? 0 );
        $lon_max = max( $_POST['bounds']['lon'] ?? 0 );

        $query .= ' AND ( lat BETWEEN ' . $lat_min . ' AND ' . $lat_max . ' )' .
                  ' AND ( lon BETWEEN ' . $lon_min . ' AND ' . $lon_max . ' )';

        $navaids_query = $query;

    }

    if( array_key_exists( 'types', $_POST ) ) {

        $query .= ' AND type IN ( "' . implode( '", "', $types ) . '" )';

    }

    if( array_key_exists( 'types_not', $_POST ) ) {

        $query .= ' AND type NOT IN ( "' . implode( '", "', $types_not ) . '" )';

    }

    if( array_key_exists( 'service', $_POST ) ) {

        $query .= ' AND service = ' . +!!( $_POST['service'] );

    }

    if( array_key_exists( 'timezone', $_POST ) ) {

        $query .= ' AND timezone = "' . $_POST['timezone'] . '"';

    }

    api_exit( [
        'query' => $query,
        'airports' => $DB->query( '
            SELECT   ICAO, name, lat, lon, alt, type, restriction
            FROM     ' . DB_PREFIX . 'airport
            WHERE    ' . $query . '
            ORDER BY ' . ( $_POST['orderby'] ?? 'tier DESC' ) . '
            LIMIT    0, ' . ( $_POST['limit'] ?? 100 )
        )->fetch_all( MYSQLI_ASSOC ),
        'navaids' => ( $_POST['navaids'] ?? 0 ) == 1 ? $DB->query( '
            SELECT  ident, type, name, frequency, lat, lon, alt
            FROM    ' . DB_PREFIX . 'navaid
            WHERE   ' . $navaids_query . '
            LIMIT   0, 50
        ' )->fetch_all( MYSQLI_ASSOC ) : []
    ] );

?>