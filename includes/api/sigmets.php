<?php

    require_once __DIR__ . '/api.php';

    $affected = 0;

    foreach( array_merge(
        json_decode( file_get_contents(
            'https://www.aviationweather.gov/cgi-bin/json/IsigmetJSON.php?bbox=-180,-90,180,90'
        ), true )['features'],
        json_decode( file_get_contents(
            'https://www.aviationweather.gov/cgi-bin/json/SigmetJSON.php'
        ), true )['features']
    ) as $sigmet ) {

        if( !array_key_exists( 'id', $sigmet ) ) {

            continue;

        }

        $_id = $sigmet['id'];
        $prop = $sigmet['properties'];

        $airport = $prop['icaoId'] ?? 'NULL';

        $fir = $prop['firId'] ?? 'NULL';
        $name = $prop['firName'] ?? 'NULL';
        $series = $prop['seriesId'] ?? 'NULL';

        $hazard = substr( strtoupper( $prop['hazard'] ?? 'UNK' ), 0, 4 );
        $qualifier = $prop['qualifier'] ?? 'NULL';
        $severity = $prop['severity'] ?? 'NULL';

        $valide_from = str_replace( [ 'T', 'Z' ], [ ' ', '' ], $prop['validTimeFrom'] );
        $valide_to = str_replace( [ 'T', 'Z' ], [ ' ', '' ], $prop['validTimeTo'] );

        $low_1 = $prop['base'] ?? ( $prop['altitudeLow1'] ?? 'NULL' );
        $low_2 = $prop['altitudeLow2'] ?? 'NULL';
        $hi_1 = $prop['top'] ?? ( $prop['altitudeHi1'] ?? 'NULL' );
        $hi_2 = $prop['altitudeHi2'] ?? 'NULL';

        $dir = str_replace( '-', 'NULL', $prop['dir'] ?? 'NULL' );
        $spd = is_numeric( $x = ( $prop['spd'] ?? null ) ) ? $x : 'NULL';
        $cng = $prop['chng'] ?? 'NULL';

        $raw = $prop['rawSigmet'] ?? ( $prop['rawAirSigmet'] ?? null );

        $polygon = json_encode( $sigmet['geometry']['coordinates'] ?? [], JSON_NUMERIC_CHECK );

        $affected += $DB->query( str_replace( '"NULL"', 'NULL', '
            INSERT INTO sigmet (
                _id, airport, fir, name, series,
                hazard, qualifier, severity,
                valid_from, valid_to,
                low_1, low_2, hi_1, hi_2,
                dir, spd, cng,
                raw, polygon
            ) VALUES (
                ' . $_id . ', "' . $airport . '", "' . $fir . '", "' . $name . '", "' . $series . '",
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