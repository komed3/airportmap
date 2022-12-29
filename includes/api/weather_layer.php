<?php

    require_once __DIR__ . '/api.php';

    $lat_min = min( $_POST['bounds']['lat'] ?? 0 );
    $lat_max = max( $_POST['bounds']['lat'] ?? 0 );
    $lon_min = min( $_POST['bounds']['lon'] ?? 0 );
    $lon_max = max( $_POST['bounds']['lon'] ?? 0 );

    api_exit( [
        'stations' => $DB->query( '
            SELECT   a.ICAO, a.name, a.lat, a.lon, a.alt,
                     m.flight_cat AS cat, m.wind_dir, m.wind_spd
            FROM     ' . DB_PREFIX . 'airport a,
                     ' . DB_PREFIX . 'metar m
            WHERE    m.station = a.ICAO
            AND      ( a.lat BETWEEN ' . $lat_min . ' AND ' . $lat_max . ' )
            AND      ( a.lon BETWEEN ' . $lon_min . ' AND ' . $lon_max . ' )
            AND      m.reported >= DATE_SUB( NOW(), INTERVAL ' . ( $_POST['maxage'] ?? 1 ) . ' DAY )
            ORDER BY ' . ( $_POST['orderby'] ?? 'a.tier DESC' ) . '
            LIMIT    0, ' . ( $_POST['limit'] ?? 50 )
        )->fetch_all( MYSQLI_ASSOC )
    ] );

?>