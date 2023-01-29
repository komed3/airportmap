<?php

    require_once __DIR__ . '/api.php';

    $traffic = [];

    foreach( json_decode( file_get_contents(
        'https://opensky-network.org/api/states/all?lamin=-90&lomin=-180&lamax=90&lomax=180&extended=1'
    ), true )['states'] as $state ) {

        $traffic[] = implode( ', ', array_map( function( $f ) {
            return $f == null ? 'NULL' : '"' . trim( $f ) . '"';
        }, [
            $state[0], // IDENT (ICAO24)
            $state[1] ?? null, // CALLSIGN
            $state[17] ?? 0, // AIRCRAFT TYPE
            date( 'Y-m-d H:i:s', $state[3] ?? time() ), // TIMESTAMP
            date( 'Y-m-d H:i:s', $state[4] ?? time() ), // CONTACT
            $state[2] ?? null, // ORIGIN (COUNTRY)
            $state[6] ?? null, // LAT
            $state[5] ?? null, // LON
            empty( $state[7] ) ? null : $state[7] * 3.281, // ALT (BARO)
            empty( $state[13] ) ? null : $state[13] * 3.281, // ALT (GEO)
            $state[10] ?? null, // HDG (TRACK)
            empty( $state[9] ) ? null : $state[9] * 1.944, // VELOCITY
            empty( $state[11] ) ? null : $state[11] * 1.944, // VERTICAL RATE
            +!!( $state[8] ?? 0 ), // ON GROUND?
            $state[14] ?? null, // SQUAWK CODE
            +!!( $state[15] ?? 0 ), // SPI
            $state[16] ?? 0, // DATA SOURCE
            [
                0 => 2, 1 => 2, 2 => 2, 3 => 7,
                4 => 8, 5 => 8, 6 => 8, 7 => 4,
                8 => 5, 9 => 6, 10 => 4, 11 => 3,
                12 => 4, 13 => 0, 14 => 1, 15 => 5,
                16 => 1, 17 => 1, 18 => 2, 19 => 0,
                20 => 0
            ][ $state[17] ?? 0 ] // TIER
        ] ) );

    }

    $DB->query( '
        TRUNCATE ' . DB_PREFIX . 'traffic
    ' );

    $DB->query( '
        INSERT INTO traffic (
            ident, callsign, type, timestamp, contact, origin,
            lat, lon, alt, alt_geo, hdg, velocity, vrate, ground,
            squawk, spi, source
        ) VALUES ( ' . implode( ' ), ( ', $traffic ) . ' );
    ' );

    api_exit( [
        'traffic' => 'updated',
        'states' => count( $traffic )
    ] );

?>