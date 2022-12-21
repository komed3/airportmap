<?php

    define( 'NO_TOKEN', true );

    require_once __DIR__ . '/../apm.php';

    if( API_KEY != $_GET['api_key'] ?? null )
        die( 'Authentication required. Access denied.' );

    $sigmets = [];

    foreach( array_slice( json_decode( file_get_contents(
        'https://www.aviationweather.gov/cgi-bin/json/IsigmetJSON.php?bbox=-90,-180,90,180'
    ), true )['features'], 1 ) as $sigmet ) {

        $sigmets[ $sigmet['id'] ] = str_replace( '"NULL"', 'NULL',
            $sigmet['id'] . ',
            "' . ( $sigmet['properties']['icaoId'] ?? 'NULL' ) . '",
            "' . ( $sigmet['properties']['firId'] ?? 'NULL' ) . '",
            "' . ( $sigmet['properties']['firName'] ?? 'NULL' ) . '",
            "' . ( $sigmet['properties']['seriesId'] ?? 'NULL' ) . '",
            "' . $sigmet['properties']['hazard'] . '",
            "' . $sigmet['properties']['qualifier'] . '",
            "' . date(
                'Y-m-d H:i:s',
                strtotime( $sigmet['properties']['validTimeFrom'] )
            ) . '",
            "' . date(
                'Y-m-d H:i:s',
                strtotime( $sigmet['properties']['validTimeTo'] )
            ) . '",
            ' . ( $sigmet['properties']['base'] ?? 'NULL' ) . ',
            ' . ( $sigmet['properties']['top'] ?? 'NULL' ) . ',
            "' . ( $sigmet['properties']['dir'] ?? 'NULL' ) . '",
            ' . ( is_numeric( $x = ( $sigmet['properties']['spd'] ?? '_' ) ) ? $x : 'NULL' ) . ',
            "' . ( $sigmet['properties']['chng'] ?? 'NULL' ) . '",
            "' . $sigmet['properties']['rawSigmet'] . '",
            "' . json_encode(
                $sigmet['geometry']['coordinates'] ?? [],
                JSON_NUMERIC_CHECK
            ) . '"'
        );

    }

    $DB->query( '
        DELETE FROM ' . DB_PREFIX . 'sigmet
        WHERE       _id IN ( ' . implode( ', ', array_keys( $sigmets ) ) . ' )
    ' );

    $DB->query( '
        INSERT INTO ' . DB_PREFIX . 'sigmet (
            _id, airport, fir, name, series, hazard, qualifier,
            valid_from, valid_to, base, top, dir, spd, chng,
            raw, polygon
        ) VALUES (
            ' . implode( ' ), ( ', array_values( $sigmets ) ) . '
        );
    ' );

?>