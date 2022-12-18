<?php

    require_once __DIR__ . '/../apm.php';

    $lat = (float) $_POST['lat'] ?? 0;
    $lon = (float) $_POST['lon'] ?? 0;

    echo json_encode( [
        'airports' => $DB->query( '
            SELECT   *, (
                3959 *
                acos( cos( radians( ' . $lat . ' ) ) *
                cos( radians( lat ) ) *
                cos( radians( lon ) -
                radians( ' . $lon . ' ) ) +
                sin( radians( ' . $lat . ' ) ) *
                sin( radians( lat ) ) )
            ) AS distance
            FROM     airport
            WHERE    ICAO != "' . ( $_POST['ident'] ?? '' ) . '"
            AND      lat BETWEEN ' . ( $lat - 5 ) . ' AND ' . ( $lat + 5 ) . '
            AND      lon BETWEEN ' . ( $lon - 5 ) . ' AND ' . ( $lon + 5 ) . '
            ORDER BY distance ASC
            LIMIT    0, ' . ( $_POST['limit'] ?? 24 )
        )->fetch_all( MYSQLI_ASSOC )
    ], JSON_NUMERIC_CHECK );

?>