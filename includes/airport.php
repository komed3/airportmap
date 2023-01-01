<?php

    function airport_by(
        string $column,
        string $ident
    ) {

        global $DB;

        return ( ( $res = $DB->query( '
            SELECT  *
            FROM    ' . DB_PREFIX . 'airport
            WHERE   ' . $column . ' = "' . $ident . '"
        ' ) ) && $res->num_rows > 0 )
            ? $res->fetch_assoc()
            : null;

    }

    function airport_link(
        array $airport
    ) {

        return '<a href="' . SITE . 'airport/' . $airport['ICAO'] . '">' . $airport['name'] . '</a>';

    }

    function airport_image(
        string $ICAO
    ) {

        global $DB;

        return ( $res = $DB->query( '
            SELECT   *
            FROM     ' . DB_PREFIX . 'image
            WHERE    airport = "' . $ICAO . '"
            ORDER BY _touched DESC
            LIMIT    0, 1
        ' ) )->num_rows == 1
            ? $res->fetch_assoc()
            : null;

    }

    function airport_weather(
        array $airport,
        int $max_deg = 10,
        int $max_age = 1
    ) {

        global $DB;

        $first = [];

        if( ( $res = $DB->query( '
            SELECT   metar.*, ( 3440.29182 * acos(
                cos( radians( ' . $airport['lat'] . ' ) ) *
                cos( radians( lat ) ) *
                cos(
                    radians( lon ) -
                    radians( ' . $airport['lon'] . ' )
                ) +
                sin( radians( ' . $airport['lat'] . ' ) ) *
                sin( radians( lat ) )
            ) ) AS distance
            FROM     ' . DB_PREFIX . 'metar,
                     ' . DB_PREFIX . 'airport
            WHERE    station = ICAO
            AND      lat BETWEEN ' . ( $airport['lat'] - $max_deg ) . ' AND ' . ( $airport['lat'] + $max_deg ) . '
            AND      lon BETWEEN ' . ( $airport['lon'] - $max_deg ) . ' AND ' . ( $airport['lon'] + $max_deg ) . '
            AND      reported >= DATE_SUB( NOW(), INTERVAL ' . $max_age . ' DAY )
            ORDER BY distance ASC
            LIMIT    0, 10
        ' ) )->num_rows > 0 ) {

            while( $row = $res->fetch_assoc() ) {

                if( empty( $first ) ) {

                    $first = $row;

                }

                if( $row->flight_cat != null ) {

                    return $row;

                }

            }

        }

        return $first;

    }

    function alt_in(
        float $altitude,
        string $in = 'ft'
    ) {

        return round( $altitude ) . '&#8239;' . $in;

    }

    function format_freq(
        float $frequency
    ) {

        if( $frequency > 1000 ) {

            $frequency /= 1000;
            $suffix = 'MHz';

        } else {

            $suffix = 'kHz';

        }

        return round( $frequency, 3 ) . '&#8239;' . $suffix;

    }

    function __DMS(
        float $decimal,
        string $type = 'lat'
    ) {

        $abs = abs( $decimal );
        $deg = floor( $abs );
        $sub = ( $abs - $deg ) * 60;
        $min = floor( $sub );
        $sec = floor( ( $sub - $min ) * 60 );

        return $deg . '°&#8239;' . $min . '′&#8239;' . $sec . '″&#8239;' . i18n( 'sdir-' . [
            'lat' => $dec < 0 ? 'S' : 'N',
            'lon' => $dec < 0 ? 'W' : 'E'
        ][ $type ] );

    }

    function __DMS_coords(
        float $lat,
        float $lon
    ) {

        return '<span>' . __DMS( $lat, 'lat' ) . '</span> ' .
               '<span>' . __DMS( $lon, 'lon' ) . '</span>';

    }

    function __morse(
        string $input,
        bool $decode = false
    ) {

        $alphabet = [
            'a' => '.-', 'b' => '-...', 'c' => '-.-.', 'd' => '-..',
            'e' => '.', 'f' => '..-.', 'g' => '--.', 'h' => '....',
            'i' => '..', 'j' => '.---', 'k' => '-.-', 'l' => '.-..',
            'm' => '--', 'n' => '-.', 'o' => '---', 'p' => '.--.',
            'q' => '--.-', 'r' => '.-.', 's' => '...', 't' => '-',
            'u' => '..-', 'v' => '...-', 'w' => '.--', 'x' => '-..-',
            'y' => '-.--', 'z' => '--..', '1' => '.----', '2' => '..---',
            '3' => '...--', '4' => '....-', '5' => '.....', '6' => '-....',
            '7' => '--...', '8' => '---..', '9' => '----.', '0' => '-----'
        ];

        return $decode
            ? implode( '', array_map(
                function( $l ) use ( $alphabet ) {
                    return array_search( $l, $alphabet );
                },
                explode( ' ', str_replace( '·', '.', $input ) )
            ) )
            : str_replace( '.', '·', implode( ' ', array_map(
                function( $l ) use ( $alphabet ) {
                    return $alphabet[ $l ];
                },
                str_split( strtolower( $input ) )
            ) ) );

    }

    function region_name(
        string $type,
        string $region
    ) {

        global $DB;

        return ( $res = $DB->query( '
            SELECT  name
            FROM    ' . DB_PREFIX . $type . '
            WHERE   code = "' . $region . '"
        ' ) )->num_rows == 1
            ? $res->fetch_object()->name
            : $region;

    }

    function region_link(
        string $type,
        string $region
    ) {

        return '<a href="' . SITE . $type . '/' . $region . '">' .
            region_name( $type, $region ) . '</a>';

    }

?>