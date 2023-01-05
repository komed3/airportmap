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

    function airport_type_link(
        string $type
    ) {

        return '<a href="' . SITE . 'type/' . $type . '">' . i18n( 'airport-type-' . $type ) . '</a>';

    }

    function airport_res_link(
        string $res
    ) {

        return '<a href="' . SITE . 'restriction/' . $res . '">' . i18n( 'airport-res-' . $res ) . '</a>';

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

    function airport_search(
        string $word,
        int $limit = -1,
        int $offset = 0
    ) {

        global $DB;

        $word = strtolower( trim( $word ) );

        return $DB->query( '
            SELECT   *
            FROM     ' . DB_PREFIX . 'airport
            WHERE    CONVERT( ICAO USING utf8 ) LIKE "%' . $word . '%"
            OR       CONVERT( IATA USING utf8 ) LIKE "%' . $word . '%"
            OR       CONVERT( GPS USING utf8 ) LIKE "%' . $word . '%"
            OR       CONVERT( LOCAL USING utf8 ) LIKE "%' . $word . '%"
            OR       CONVERT( name USING utf8 ) LIKE "%' . $word . '%"
            ORDER BY tier DESC
            ' . ( $limit >= 0
                ? 'LIMIT ' . $offset . ', ' . $limit
                : ''
            )
        )->fetch_all( MYSQLI_ASSOC );

    }

    function airport_nearest(
        float $lat,
        float $lon,
        array $options = [],
        int $max_deg = 10,
        int $limit = 99999
    ) {

        global $DB;

        $query = implode( ' AND ', array_filter( array_map( function( $o ) {
            return $o[1] ? $o[0] . ' ' . ( $o[2] ?? '=' ) . ' ' . $o[1] : null;
        }, $options ) ) );

        return $DB->query( '
            SELECT  *, ( 3440.29182 * acos(
                cos( radians( ' . $lat . ' ) ) *
                cos( radians( lat ) ) *
                cos(
                    radians( lon ) -
                    radians( ' . $lon . ' )
                ) +
                sin( radians( ' . $lat . ' ) ) *
                sin( radians( lat ) )
            ) ) AS distance
            FROM     airport
            WHERE    ( lat BETWEEN ' . ( $lat - $max_deg ) . ' AND ' . ( $lat + $max_deg ) . ' )
            AND      ( lon BETWEEN ' . ( $lon - $max_deg ) . ' AND ' . ( $lon + $max_deg ) . ' )
            AND      ' . $query . '
            ORDER BY distance ASC
            LIMIT    0, ' . $limit
        )->fetch_all( MYSQLI_ASSOC );

    }

    function airport_weather(
        array $airport,
        int $max_deg = 10,
        int $max_age = 1
    ) {

        global $DB;

        $first = [];

        if( ( $res = $DB->query( '
            SELECT   m.*, a.gmt_offset, ( 3440.29182 * acos(
                cos( radians( ' . $airport['lat'] . ' ) ) *
                cos( radians( a.lat ) ) *
                cos(
                    radians( a.lon ) -
                    radians( ' . $airport['lon'] . ' )
                ) +
                sin( radians( ' . $airport['lat'] . ' ) ) *
                sin( radians( a.lat ) )
            ) ) AS distance
            FROM     ' . DB_PREFIX . 'metar m,
                     ' . DB_PREFIX . 'airport a
            WHERE    m.station = a.ICAO
            AND      a.lat BETWEEN ' . ( $airport['lat'] - $max_deg ) . ' AND ' . ( $airport['lat'] + $max_deg ) . '
            AND      a.lon BETWEEN ' . ( $airport['lon'] - $max_deg ) . ' AND ' . ( $airport['lon'] + $max_deg ) . '
            AND      m.reported >= DATE_SUB( NOW(), INTERVAL ' . $max_age . ' DAY )
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

    function airport_warn(
        array $airport
    ) {

        global $DB;

        if( ( $res = $DB->query( '
            SELECT  *
            FROM    ' . DB_PREFIX . 'sigmet
            WHERE   airport = "' . $airport['ICAO'] . '"
            AND     valid_from <= NOW()
            AND     valid_to >= NOW()
        ' ) )->num_rows > 0 ) {

            $sigmet = $res->fetch_assoc();

            return '<div class="airport-warn content-normal hazard-' . $sigmet['hazard'] . '">
                <div class="info">
                    <div class="hazard">' . sigmet_hazard( $sigmet ) . '</div>
                    <div class="valid">' . sigmet_valid( $sigmet ) . '</div>
                </div>
                <a href="' . SITE . 'weather/sigmets" class="link">
                    <span>' . i18n( 'read-more' ) . '</span>
                </a>
            </div>';

        }

        return '';

    }

    function _airport_warn(
        array $airport
    ) {

        echo airport_warn( $airport );

    }

    function airport_list(
        array $airports,
        int $page = 1,
        string $baseurl = '',
        array $point = []
    ) {

        if( count( $airports ) == 0 ) {

            $content = '<div class="empty">
                <i class="icon">flight_takeoff</i>
                <span class="label">' . i18n( 'search-results-empty' ) . '</span>
            </div>';

        } else {
        
            $pagination = $page == -1 ? '' : pagination( count( $airports ), $page, $baseurl );

            $content = $pagination . '<div class="list">';

            foreach( array_slice( $airports, ( $page - 1 ) * 24, 24 ) as $airport ) {

                $content .= '<div class="row airport-' . $airport['type'] . ' restrict-' . $airport['restriction'] . ' service-' . $airport['service'] . '">
                    <mapicon></mapicon>
                    <div class="info">
                        <div class="headline">
                            <b class="code">' . $airport['ICAO'] . '</b>
                            <a href="' . SITE . 'airport/' . $airport['ICAO'] . '" class="name">' . $airport['name'] . '</a>
                        </div>
                        <div class="location">
                            ' . __DMS_coords( $airport['lat'], $airport['lon'] ) . '
                            <span class="divider">/</span>
                            <span>' . alt_in( $airport['alt'], 'ft' ) . '</span>
                            <span>(' . alt_in( $airport['alt'] / 3.281, 'm&nbsp;MSL' ) . ')</span>
                        </div>
                        <div class="region">
                            ' . region_link( 'country', $airport['country'] ) . '
                            <span class="divider">/</span>
                            ' . region_link( 'region', $airport['region'] ) . '
                        </div>
                        <div class="tags">
                            ' . airport_type_link( $airport['type'] ?? 'unknown' ) . '
                            ' . airport_res_link( $airport['restriction'] ?? 'public' ) . '
                            ' . ( $airport['service'] ? '<span>' . i18n( 'airline-service' ) . '</span>' : '' ) . '
                        </div>
                    </div>
                    ' . ( !empty( $point ) ? __nearby( $airport['distance'], [
                        $airport['lat'], $airport['lon']
                    ], $point ) : '' ) . '
                </div>';

            }

            $content .= '</div>' . $pagination;

        }

        return '<div class="airportlist">
            ' . $content . '
        </div>';

    }

    function _airport_list(
        array $airports,
        int $page = 1,
        string $baseurl = '',
        array $point = []
    ) {

        echo airport_list( $airports, $page, $baseurl, $point );

    }

    function radio_list(
        array $airport,
        array $radios
    ) {

        $list = '';

        foreach( $radios as $radio ) {

            $list .= '<div class="radio radio-' . $radio['type'] . '">
                <div class="type">' . $radio['type'] . '</div>
                <div class="info">
                    <div class="freq">' . format_freq( $radio['frequency'] ) . '</div>
                    <div class="label">' . i18n( 'radio-' . $radio['type'] ) . '</div>
                </div>
            </div>';

        }

        return '<div class="radiolist">
            ' . $list . '
        </div>';

    }

    function _radio_list(
        array $airport,
        array $radios
    ) {

        echo radio_list( $airport, $radios );

    }

    function runway_list(
        array $airport,
        array $runways
    ) {

        $list = '';

        foreach( $runways as $runway ) {

            $list .= '<div class="runway">
                ' . __HDG_bug( $runway['l_hdg'] ?? -1 ) . '
                <div class="info">
                    <div class="headline">
                        <span class="state state-' . $runway['inuse'] . '">
                            ' . i18n( 'runway-state-' . $runway['inuse'] ) . '
                        </span>
                        <b class="ident">' . $runway['ident'] . '</b>
                    </div>
                    <div class="site">
                        ' . ( $runway['length'] ? '<div class="size">
                            <i class="icon">crop_free</i>
                            <span>' . alt_in( $runway['length'], 'ft' ) . '</span>
                            ' . ( $runway['width'] ? '<span class="divider">×</span>
                            <span>' . alt_in( $runway['width'], 'ft' ) . '</span>' : '' ) . '
                        </div>' : '' ) . ( $runway['l_alt'] ? '<div class="alt">
                            <i class="icon">flight_takeoff</i>
                            <span>' . alt_in( $runway['l_alt'], 'ft' ) . '</span>
                            <span>(' . alt_in( $runway['l_alt'] / 3.281, 'm&nbsp;MSL' ) . ')</span>
                        </div>' : '' ) . '
                    </div>
                    <div class="condition">
                        <div class="surface">
                            <i class="icon">dehaze</i>
                            <span>' . i18n( 'surface-' . $runway['surface'] ) . '</span>
                        </div>
                        <div class="lighting ' . ( $runway['lighted'] ? 'lighted' : '' ) . '">
                            <i class="icon">lightbulb</i>
                            <span>' . i18n( 'runway-lighted-' . $runway['lighted'] ) . '</span>
                        </div>
                    </div>
                    ' . ( $runway['l_dthr'] ? '<div class="dthr">
                        <span>
                            <i class="icon">texture</i>
                            <span>' . i18n( 'displaced-threshold-runway',
                                $runway[ 'l_ident' ],
                                alt_in( $runway['l_dthr'], 'ft' )
                            ) . '</span>
                        </span>
                    </div>' : '' ) . '
                    ' . ( $runway['r_dthr'] ? '<div class="dthr">
                        <span>
                            <i class="icon">texture</i>
                            <span>' . i18n( 'displaced-threshold-runway',
                                $runway[ 'r_ident' ],
                                alt_in( $runway['r_dthr'], 'ft' )
                            ) . '</span>
                        </span>
                    </div>' : '' ) . '
                </div>
            </div>';

        }

        return '<div class="runwaylist">
            ' . $list . '
        </div>';

    }

    function _runway_list(
        array $airport,
        array $runways
    ) {

        echo runway_list( $airport, $runways );

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

    function __cardinal(
        float $hdg,
        bool $short = true
    ) {

        return i18n( ( $short ? 's' : '' ) . 'dir-' . [
            'N', 'NNE', 'NE', 'ENE',
            'E', 'ESE', 'SE', 'SSE',
            'S', 'SSW', 'SW', 'WSW',
            'W', 'WNW', 'NW', 'NNW',
            'N'
        ][ round( $hdg / 22.5 ) ] );

    }

    function __HDG(
        float $p1_lat,
        float $p1_lon,
        float $p2_lat,
        float $p2_lon
    ) {

        $p1_lat = $p1_lat * M_PI / 180;
        $p1_lon = $p1_lon * M_PI / 180;
        $p2_lat = $p2_lat * M_PI / 180;
        $p2_lon = $p2_lon * M_PI / 180;

        $delta_lon = $p2_lon - $p1_lon;

        $X = cos( $p2_lat ) * sin( $delta_lon );
        $Y = cos( $p1_lat ) * sin( $p2_lat ) - sin( $p1_lat ) *
             cos( $p2_lat ) * cos( $delta_lon );

        return ( ( atan2( $X, $Y ) * 180 / M_PI ) + 180 ) % 360;

    }

    function __HDG_bug(
        float $hdg = 0,
        string $empty = ''
    ) {

        return '<div class="heading ' . ( $hdg < 0 ? 'no-hdg' : '' ) . '">
            <div class="bug" style="transform: rotate( ' . $hdg . 'deg );">
                <i class="icon">navigation</i>
            </div>
            <div class="deg">' . round( $hdg ) . '°</div>
        </div>';

    }

    function __nearby(
        float $dist,
        array $from,
        array $to
    ) {

        $hdg = __HDG( $from[0], $from[1], $to[0], $to[1] );

        return '<div class="nearby">
            ' . __HDG_bug( $hdg ) . '
            <div class="meta">
                <div class="label">' . __cardinal( $hdg ) . '</div>
                <div class="dist">' . round( $dist ) . '&nbsp;nm</div>
            </div>
        </div>';

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
            ? $res->fetch_object()->name ?? i18n( 'unknown' )
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