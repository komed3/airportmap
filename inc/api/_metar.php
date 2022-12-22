<?php

    define( 'NO_TOKEN', true );

    require_once __DIR__ . '/../apm.php';

    api_auth( '_metar' );

    $ceiling = [ 'SKC', 'SCT', 'BKN', 'OVC', 'OVX' ];
    $delete = $insert = [];

    foreach( explode( PHP_EOL, file_get_contents(
        'https://www.aviationweather.gov/adds/dataserver_current/current/metars.cache.csv'
    ) ) as $metar ) {

        $data = explode( ',', $metar );

        if( count( $data ) != 44 || $data[1] == 'station_id' || strlen( $data[17] ) ) {

            continue;

        }

        $station = strtoupper( $data[1] );

        $delete[ $station ] = $station;

        $vis_vert = strlen( $data[41] ) ? (int) $data[41] : (
            in_array( $data[22], $ceiling ) ? (int) $data[23] : (
                in_array( $data[24], $ceiling ) ? (int) $data[25] : (
                    in_array( $data[26], $ceiling ) ? (int) $data[27] : (
                        in_array( $data[28], $ceiling ) ? (int) $data[29] : null
                    )
                )
            )
        );

        $insert[ $station ] = implode( ', ', array_map( function( $f ) {
            return $f == null ? 'NULL' : '"' . trim( $f ) . '"';
        }, [
            // GENERAL
                $station, // ICAO
                $data[0], // RAW
                str_replace( [ 'T', 'Z' ], [ ' ', '' ], $data[2] ), // DATE AND TIME
                strlen( $data[21] ) ? $data[21] : null, // WX
            // TEMP
                (float) $data[5], // TEMP (C)
                (float) $data[6], // DEW POINT (C)
            // ALTIM
                strlen( $data[11] ) ? (float) $data[11] : null, // ALTIM (HQ)
                strlen( $data[12] ) ? (float) $data[12] : null, // ALTIM SEA LEVEL (MB)
            // WIND
                strlen( $data[7] ) ? (int) $data[7] : null, // WIND DIR (DEG)
                strlen( $data[8] ) ? (int) $data[8] : null, // WIND SPEED (KT)
                strlen( $data[9] ) ? (int) $data[9] : null, // WIND GUST (KT)
            // PRECIP
                (float) $data[36], // RAIN (IN)
                (float) $data[40], // SNOW (IN)
            // SKY CONT
                strlen( $data[10] ) ? (float) $data[10] : null, // VISIBILITY (MI)
                $vis_vert, // VERTICAL VIS. (FT)
                $data[30] ?? null, // FLIGHT CAT
            // CLOUD LAYERS
                // LAYER 1
                    strlen( $data[22] ) ? $data[22] : null, // COVER
                    strlen( $data[23] ) ? (int) $data[23] : null, // BASE (FT)
                // LAYER 1
                    strlen( $data[24] ) ? $data[24] : null, // COVER
                    strlen( $data[25] ) ? (int) $data[25] : null, // BASE (FT)
                // LAYER 1
                    strlen( $data[26] ) ? $data[26] : null, // COVER
                    strlen( $data[27] ) ? (int) $data[23] : null, // BASE (FT)
                // LAYER 1
                    strlen( $data[28] ) ? $data[28] : null, // COVER
                    strlen( $data[29] ) ? (int) $data[25] : null, // BASE (FT)
        ] ) );

    }

    $DB->query( '
        DELETE FROM ' . DB_PREFIX . 'metar
        WHERE       station IN (
            "' . implode( '", "', $delete ) . '"
        )
    ' );

    $affected = $DB->query( '
        INSERT INTO metar (
            station, raw, reported, wx,
            temp, dewp, altim, altim_sea,
            wind_dir, wind_spd, wind_gust,
            precip, snow,
            vis_horiz, vis_vert, flight_cat,
            cloud_1_cover, cloud_1_base,
            cloud_2_cover, cloud_2_base,
            cloud_3_cover, cloud_3_base,
            cloud_4_cover, cloud_4_base
        ) VALUES ( ' . implode( ' ), ( ', $insert ) . ' );
    ' );

    $DB->close();

    echo json_encode( [
        'request' => '_metar',
        'status' => 'success',
        'affected' => count( $insert )
    ], JSON_NUMERIC_CHECK );

?>