<?php

    global $DB, $airport;

    if( empty( $airport ) ) {

        __404();

    }

    $stations = $DB->query( '
        SELECT   *, ( 3440.29182 * acos(
            cos( radians( ' . $airport['lat'] . ' ) ) *
            cos( radians( a.lat ) ) *
            cos(
                radians( a.lon ) -
                radians( ' . $airport['lon'] . ' )
            ) +
            sin( radians( ' . $airport['lat'] . ' ) ) *
            sin( radians( a.lat ) )
        ) ) AS distance, TIMESTAMPDIFF(
            MINUTE, m.reported, CURRENT_TIMESTAMP()
        ) AS age
        FROM     ' . DB_PREFIX . 'metar m,
                 ' . DB_PREFIX . 'airport a
        WHERE    m.station = a.ICAO
        AND      (
            a.lat BETWEEN ' . ( $airport['lat'] - 15 ) . '
            AND ' . ( $airport['lat'] + 15 ) . '
        )
        AND      (
            a.lon BETWEEN ' . ( $airport['lon'] - 15 ) . '
            AND ' . ( $airport['lon'] + 15 ) . '
        )
        AND      m.reported >= DATE_SUB( NOW(), INTERVAL 1 DAY )
        ORDER BY ( distance + age ) ASC
        LIMIT    0, 10
    ' )->fetch_all( MYSQLI_ASSOC );

?>