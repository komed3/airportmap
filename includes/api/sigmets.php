<?php

    require_once __DIR__ . '/api.php';

    $affected = 0;

    foreach( array_merge(
        ( array ) json_decode( file_get_contents( 'https://aviationweather.gov/api/data/isigmet?format=json' ), true ) ?? [],
        ( array ) json_decode( file_get_contents( 'https://aviationweather.gov/api/data/sigmet?format=json' ), true ) ?? [],
        ( array ) json_decode( file_get_contents( 'https://aviationweather.gov/api/data/airsigmet?format=json' ), true ) ?? []
    ) as $sigmet ) {

        $raw = $sigmet['rawSigmet'] ?? $sigmet['rawAirSigmet'] ?? null;
        if( ! $raw ) continue;

        $_id = hash( 'sha256', $raw );
        $airport = $sigmet['icaoId'] ?? 'NULL';

        $fir = $sigmet['firId'] ?? 'NULL';
        $name = $sigmet['firName'] ?? 'NULL';
        $series = $sigmet['seriesId'] ?? 'NULL';

        $hazard = substr( strtoupper( $sigmet['hazard'] ?? 'UNK' ), 0, 4 );
        $qualifier = $sigmet['qualifier'] ?? 'NULL';
        $severity = $sigmet['severity'] ?? 'NULL';

        $valide_from = date( 'Y-m-d H:i:s', $sigmet['validTimeFrom'] );
        $valide_to = date( 'Y-m-d H:i:s', $sigmet['validTimeTo'] );

        $low_1 = $sigmet['base'] ?? ( $sigmet['altitudeLow1'] ?? 'NULL' );
        $low_2 = $sigmet['altitudeLow2'] ?? 'NULL';
        $hi_1 = $sigmet['top'] ?? ( $sigmet['altitudeHi1'] ?? 'NULL' );
        $hi_2 = $sigmet['altitudeHi2'] ?? 'NULL';

        $dir = str_replace( '-', 'NULL', $sigmet['dir'] ?? $sigmet['movementDir'] ?? 'NULL' );
        $spd = is_numeric( $x = ( $sigmet['spd'] ?? $sigmet['movementSpd'] ?? null ) ) ? $x : 'NULL';
        $cng = $sigmet['chng'] ?? 'NULL';

        $points = [];
        foreach( $sigmet['coords'] ?? [] as $point ) {
            $points[] = [ $point['lon'], $point['lat'] ];
        }

        $polygon = json_encode( $points, JSON_NUMERIC_CHECK );

        $affected += $DB->query( str_replace( '"NULL"', 'NULL', '
            INSERT INTO sigmet (
                _id, airport, fir, name, series,
                hazard, qualifier, severity,
                valid_from, valid_to,
                low_1, low_2, hi_1, hi_2,
                dir, spd, cng,
                raw, polygon
            ) VALUES (
                "' . $_id . '", "' . $airport . '", "' . $fir . '",
                "' . $name . '", "' . $series . '",
                "' . $hazard . '", "' . $qualifier . '", ' . $severity . ',
                "' . $valide_from . '", "' . $valide_to . '",
                ' . $low_1 . ', ' . $low_2 . ', ' . $hi_1 . ', ' . $hi_2 . ',
                "' . $dir . '", ' . $spd . ', "' . $cng . '",
                "' . $raw . '", "' . $polygon . '"
            ) ON DUPLICATE KEY UPDATE
                airport = "' . $airport . '",
                fir = "' . $fir . '",
                name = "' . $name . '",
                series = "' . $series . '",
                hazard = "' . $hazard . '",
                qualifier = "' . $qualifier . '",
                severity = ' . $severity . ',
                valid_from = "' . $valide_from . '",
                valid_to = "' . $valide_to . '",
                low_1 = ' . $low_1 . ',
                low_2 = ' . $low_2 . ',
                hi_1 = ' . $hi_1 . ',
                hi_2 = ' . $hi_2 . ',
                dir = "' . $dir . '",
                spd = ' . $spd . ',
                cng = "' . $cng . '",
                raw = "' . $raw . '",
                polygon = "' . $polygon . '"
        ' ) );

    }

    api_exit( [
        'sigmets' => 'updated',
        'affected' => $affected
    ] );

?>