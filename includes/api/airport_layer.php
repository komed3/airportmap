<?php

    require_once __DIR__ . '/api.php';

    $query = $coods_query = '1';

    if( array_key_exists( 'bounds', $_POST ) ) {

        $lat_min = min( $_POST['bounds']['lat'] ?? 0 );
        $lat_max = max( $_POST['bounds']['lat'] ?? 0 );
        $lon_min = min( $_POST['bounds']['lon'] ?? 0 );
        $lon_max = max( $_POST['bounds']['lon'] ?? 0 );

        $query .= ' AND ( lat BETWEEN ' . $lat_min . ' AND ' . $lat_max . ' )' .
                  ' AND ( lon BETWEEN ' . $lon_min . ' AND ' . $lon_max . ' )';

        $coods_query = $query;

    }

    foreach( [ 'continent', 'country', 'region' ] as $col ) {

        if( array_key_exists( $col, $_POST ) ) {

            $query .= ' AND ' . $col . ' = "' . $_POST[ $col ] . '"';

        }

    }

    if( array_key_exists( 'ICAO', $_POST ) ) {

        $query .= ' AND ICAO LIKE "' . $_POST['ICAO'] . '%" ' .
                  ' AND LENGTH( ICAO ) = 4 ';

    }

    if( array_key_exists( 'types', $_POST ) ) {

        $query .= ' AND type IN ( "' . implode( '", "', $_POST['types'] ) . '" )';

    }

    if( array_key_exists( 'types_not', $_POST ) ) {

        $query .= ' AND type NOT IN ( "' . implode( '", "', $_POST['types_not'] ) . '" )';

    }

    if( array_key_exists( 'restriction', $_POST ) ) {

        $query .= ' AND restriction = "' . $_POST['restriction'] . '"';

    }

    if( array_key_exists( 'service', $_POST ) ) {

        $query .= ' AND service = ' . +!!( $_POST['service'] );

    }

    if( array_key_exists( 'timezone', $_POST ) ) {

        $query .= ' AND timezone = "' . $_POST['timezone'] . '"';

    }

    if( array_key_exists( '__', $_POST ) ) {

        $query .= $_POST['__'];

    }

    api_exit( [
        'query' => $query,
        'airports' => $DB->query( '
            SELECT   ICAO, name, lat, lon, alt, type, restriction
            FROM     ' . DB_PREFIX . 'airport
            WHERE    ' . $query . '
            ORDER BY ' . ( $_POST['orderby'] ?? 'tier DESC' ) . '
            LIMIT    0, ' . min( 100, $_POST['limit'] ?? 75 )
        )->fetch_all( MYSQLI_ASSOC ),
        'navaids' => ( $_POST['navaids'] ?? 0 ) == 1 ? $DB->query( '
            SELECT   _id, ident, type, frequency, lat, lon, alt
            FROM     ' . DB_PREFIX . 'navaid
            WHERE    ' . $coods_query . '
            LIMIT    0, ' . min( 50, $_POST['limit'] ?? 50 )
        )->fetch_all( MYSQLI_ASSOC ) : [],
        'waypoints' => ( $_POST['waypoints'] ?? 0 ) == 1 ? $DB->query( '
            SELECT   _id, ident, lat, lon
            FROM     ' . DB_PREFIX . 'waypoint
            WHERE    ' . $coods_query . '
            LIMIT    0, ' . min( 50, $_POST['limit'] ?? 50 )
        )->fetch_all( MYSQLI_ASSOC ) : []
    ] );

?>