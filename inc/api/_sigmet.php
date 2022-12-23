<?php

    define( 'NO_TOKEN', true );

    require_once __DIR__ . '/../apm.php';

    api_auth( '_sigmet' );

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

        $airport = $sigmet['properties']['icaoId'] ?? 'NULL';

        $fir = $sigmet['properties']['firId'] ?? 'NULL';
        $name = $sigmet['properties']['firName'] ?? 'NULL';
        $series = $sigmet['properties']['seriesId'] ?? 'NULL';

        $hazard = substr( strtoupper( $sigmet['properties']['hazard'] ?? 'UNK' ), 0, 4 );
        $qualifier = $sigmet['properties']['qualifier'] ?? 'NULL';
        $severity = $sigmet['properties']['severity'] ?? 'NULL';

        $valide_from = str_replace( [ 'T', 'Z' ], [ ' ', '' ], $sigmet['properties']['validTimeFrom'] );
        $valide_to = str_replace( [ 'T', 'Z' ], [ ' ', '' ], $sigmet['properties']['validTimeTo'] );

        $low_1 = $sigmet['properties']['base'] ?? ( $sigmet['properties']['altitudeLow1'] ?? 'NULL' );
        $low_2 = $sigmet['properties']['altitudeLow2'] ?? 'NULL';
        $hi_1 = $sigmet['properties']['top'] ?? ( $sigmet['properties']['altitudeHi1'] ?? 'NULL' );
        $hi_2 = $sigmet['properties']['altitudeHi2'] ?? 'NULL';

        $dir = str_replace( '-', 'NULL', $sigmet['properties']['dir'] ?? 'NULL' );
        $spd = is_numeric( $x = ( $sigmet['properties']['spd'] ?? null ) ) ? $x : 'NULL';
        $cng = $sigmet['properties']['chng'] ?? 'NULL';

        $raw = $sigmet['properties']['rawSigmet'] ?? ( $sigmet['properties']['rawAirSigmet'] ?? null );

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

    $DB->close();

    echo json_encode( [
        'request' => '_sigmet',
        'status' => 'success',
        'affected' => $affected
    ], JSON_NUMERIC_CHECK );

?>