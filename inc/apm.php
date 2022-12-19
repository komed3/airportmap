<?php

    if( defined( 'NO_TOKEN' ) || !isset( $_POST['token'] ) )
        die( 'Wrong entry point :(' );

    require_once __DIR__ . '/../config.php';

    $DB = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT );

    $DB->set_charset( DB_CHARSET );

    function airport_stats() {

        global $DB;

        $stats = [];

        foreach( $DB->query( '
            SELECT   type,
                    COUNT( ICAO ) AS cnt
            FROM     ' . DB_PREFIX . 'airport
            GROUP BY type
        ' )->fetch_all() as $type ) {

            $stats[ $type[0] ] = $type[1];

        }

        return $stats;

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
        string $ident = '',
        array $exclude_types = [ 'closed' ],
        bool $service = false,
        int $limit = 24
    ) {

        global $DB;

        return $DB->query( '
            SELECT   *, ( 3440.29182 * acos(
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
            WHERE    ICAO != "' . $ident . '"
            AND      lat BETWEEN ' . ( $lat - 10 ) . ' AND ' . ( $lat + 10 ) . '
            AND      lon BETWEEN ' . ( $lon - 10 ) . ' AND ' . ( $lon + 10 ) . '
            AND      type NOT IN ( "' . implode( '", "', $exclude_types ) . '" )
            ' . ( $service ? 'AND service = 1' : '' ) . '
            ORDER BY distance ASC
            LIMIT    0, ' . $limit
        )->fetch_all( MYSQLI_ASSOC );

    }

    function airport_search_form() {

        return '<form data-form="search" class="fullsearch" autocomplete="off">
            <input type="text" name="searchtext" data-placeholder="Search for ICAO, IATA or airport name …" />
            <button type="submit" name="searchsubmit">
                <i class="icon">travel_explore</i>
            </button>
        </form>';

    }

    function airport_list(
        array $airports = [],
        int $page = 1,
        array $point = []
    ) {

        if( count( $airports ) == 0 ) {

            $content = '<div class="empty">
                <i class="icon">flight_takeoff</i>
                <span class="label" data-i18n="Your request didn’t provide any results"></span>
            </div>';

        } else {
        
            $pagination = $page == -1 ? '' : '<div class="pagination" data-pagination="' . base64_encode( json_encode( [
                'results' => count( $airports ),
                'page' => $page
            ], JSON_NUMERIC_CHECK ) ) . '"></div>';

            $content = $pagination . '<div class="list">';

            foreach( array_slice( $airports, ( $page - 1 ) * 24, 24 ) as $airport ) {

                $content .= '<div class="row type-' . $airport['type'] . ' restrict-' . $airport['restriction'] . ' service-' . $airport['service'] . '">
                    <navicon></navicon>
                    <div class="info">
                        <div class="headline">
                            <span class="code">' . $airport['ICAO'] . '</span>
                            <a data-href="airport/' . $airport['ICAO'] . '" class="name">' . $airport['name'] . '</a>
                        </div>
                        <div class="location">
                            <span class="coord lat" data-lat="' . $airport['lat'] . '"></span>
                            <span class="coord lon" data-lon="' . $airport['lon'] . '"></span>
                            <span class="divider">/</span>
                            <span class="alt ft" data-alt="' . $airport['alt'] . '"></span>
                            <span class="alt msl" data-msl="' . $airport['alt'] . '"></span>
                        </div>
                        <div class="breadcrumbs" data-bc="T' .
                            $airport['continent'] . '::C' .
                            $airport['country'] . '::R' .
                            $airport['region'] . '::M' .
                            $airport['municipality'] . '"></div>
                        <div class="tags">
                            <span class="tag type" data-i18n="' . ucfirst( $airport['type'] ?? 'Unknown' ) . '"></span>
                            <span class="tag use" data-i18n="' . ucfirst( $airport['restriction'] ?? 'Unknown' ) . '"></span>
                            ' . ( $airport['service'] ? '<span class="tag service" data-i18n="Airline Service"></span>' : '' ) . '
                        </div>
                    </div>
                    ' . ( empty( $point ) ? '' : '<div class="nearby" data-nearby="' . base64_encode( json_encode( [
                        'p1' => [
                            'lat' => $point[0],
                            'lon' => $point[1]
                        ],
                        'p2' => [
                            'lat' => $airport['lat'],
                            'lon' => $airport['lon']
                        ],
                        'dist' => $airport['distance'] ?? -1
                    ], JSON_NUMERIC_CHECK ) ) . '">
                        <div class="heading">
                            <div class="bug"><i class="icon">navigation</i></div>
                            <div class="deg"></div>
                        </div>
                        <div class="meta">
                            <div class="label"></div>
                            <div class="dist"></div>
                        </div>
                    </div>' ) . '
                </div>';

            }

            $content .= '</div>' . $pagination;

        }

        return '<div class="airportlist">
            ' . $content . '
        </div>';

    }

    function navaid_list(
        array $navaids = []
    ) {

        foreach( $navaids as $navaid ) {

            $content .= '<div class="navaid navaid-' . $navaid['type'] . '">
                <navicon></navicon>
                <div class="info">
                    <div class="headline">
                        <span class="ident">' . $navaid['ident'] . '</span>
                        <span class="morse" data-morse="' . $navaid['ident'] . '"></span>
                    </div>
                    <div class="freq" data-freq="' . $navaid['frequency'] . '"></div>
                    <div class="line">
                        <span class="type">' . $navaid['type'] . '</span>
                        <span class="name">' . $navaid['name'] . '</span>
                    </div>
                    ' . ( empty( $navaid['level'] ) ? '' : '<div class="line">
                        <span class="usage" data-i18n="' . [
                            'BOTH' => 'High- and low-level enroute',
                            'HI' => 'High-level enroute',
                            'LO' => 'Low-level enroute',
                            'RNAV' => 'RNAV',
                            'TERMINAL' => 'Terminal-area navigation'
                        ][ $navaid['level'] ] . '"></span>
                        <span class="power" data-i18n="(' . $navaid['power'] . ')"></span>
                    </div>' ) . '
                    <div class="line">
                        <span class="coord lat" data-lat="' . $navaid['lat'] . '"></span>
                        <span class="coord lon" data-lon="' . $navaid['lon'] . '"></span>
                    </div>
                    <div class="line">
                        <span class="alt ft" data-alt="' . $navaid['alt'] . '"></span>
                        <span class="alt msl" data-msl="' . $navaid['alt'] . '"></span>
                    </div>
                </div>
            </div>';

        }

        return '<div class="navaidlist">
            ' . $content . '
        </div>';

    }

?>