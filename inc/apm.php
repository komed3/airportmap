<?php

    if( !defined( 'NO_TOKEN' ) && !isset( $_POST['token'] ) )
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

    function api_auth(
        string $request = ''
    ) {

        if( API_KEY != $_GET['api_key'] ?? null )
            die( json_encode( [
                'request' => $request,
                'status' => 'error',
                'msg' => 'Authentication required. Access denied.'
            ] ) );

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
                        <div class="breadcrumbs no-world" data-bc="T' .
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

    function radio_list(
        array $radios = []
    ) {

        foreach( $radios as $radio ) {

            $content .= '<div class="radio radio-' . $radio['type'] . '">
                <div class="type">' . $radio['type'] . '</div>
                <div class="info">
                    <div class="freq" data-freq="' . $radio['frequency'] . '"></div>
                    <div class="label" data-i18n="' . [
                        'A/A' => 'Air-To-Air',
                        'A/D' => 'Analog/Digital',
                        'A/G' => 'Air-To-Ground',
                        'AAS' => 'Airport Advisory Service',
                        'ACC' => 'Area Control Center',
                        'ACP' => 'ACP',
                        'AFIS' => 'Aerodrome Flight Information Service',
                        'APP' => 'Approach',
                        'APRON' => 'Apron',
                        'ARCAL' => 'Aircraft Radio Control of Aerodrome Lighting',
                        'ARTC' => 'Air Route Traffic Control (USA)',
                        'ASOS' => 'Automated Surface Observing System',
                        'ASOW' => 'ASOW',
                        'ATF' => 'Aerodrome Traffic Frequency',
                        'ATIS' => 'Automatic Terminal Information Service',
                        'CLD' => 'CLD',
                        'CTAF' => 'Common Traffic Advisory Frequency',
                        'DEP' => 'Departure',
                        'DIR' => 'Director',
                        'FCC' => 'Federal Communications Commission',
                        'FSS' => 'Flight Service Station',
                        'GCA' => 'Ground Controlled Approach',
                        'GROUND' => 'Ground',
                        'INFO' => 'Info',
                        'MISC' => 'Misc',
                        'OPS' => 'Operations',
                        'PAL' => 'Pilot Activated Lighting',
                        'RADAR' => 'Radar',
                        'RADIO' => 'Radio',
                        'RCO' => 'Remote Communications Outlet',
                        'TIBA' => 'Traffic Information Broadcast by Aircraft',
                        'TMA' => 'Terminal Control Area',
                        'TOWER' => 'Tower',
                        'TRAFFIC' => 'Traffic',
                        'UNICOM' => 'Aeronautical Advisory Service'
                    ][ $radio['type'] ] . '"></div>
                </div>
            </div>';

        }

        return '<div class="radiolist">
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

    function runway_list(
        array $runways = []
    ) {

        foreach( $runways as $runway ) {

            $content .= '<div class="runway">
                <div class="heading" data-hdg="' . $runway['l_hdg'] . '">
                    <div class="bug"><i class="icon">navigation</i></div>
                    <div class="deg"></div>
                </div>
                <div class="info">
                    <div class="headline">
                        <div class="state ' .
                            ( $runway['inuse'] ? 'inuse' : 'inop' ) . '" data-i18n="' .
                            ( $runway['inuse'] ? 'IN USE' : 'INOP' ) . '"></span>
                        </div>
                        <div class="ident">' . $runway['ident'] . '</div>
                    </div>
                    ' . ( $runway['length'] ? '<div class="size">
                        <span class="alt ft" data-alt="' . $runway['length'] . '"></span>
                        ' . ( $runway['width'] ? '<span class="divider">×</span>
                        <span class="alt ft" data-alt="' . $runway['width'] . '"></span>' : '' ) . '
                        ' . ( $runway['l_alt'] ? '<span class="divider">/</span>
                        <span class="alt ft" data-alt="' . $runway['l_alt'] . '"></span>
                        <span class="alt msl" data-msl="' . $runway['l_alt'] . '"></span>' : '' ) . '
                    </div>' : '' ) . '
                    <div class="condition">
                        <div class="surface surface-' . $runway['surface'] . '">
                            <i class="icon">flight_takeoff</i>
                            <span data-i18n="' . [
                                'ASP' => 'Asphalt',
                                'BIT' => 'Bituminous asphalt or tarmac',
                                'BRI' => 'Bricks (no longer in use, covered with asphalt or concrete now)',
                                'CLA' => 'Clay',
                                'COM' => 'Composite',
                                'CON' => 'Concrete',
                                'COP' => 'Composite',
                                'COR' => 'Coral (fine crushed coral reef structures)',
                                'GRE' => 'Graded or rolled earth, grass on graded earth',
                                'GRS' => 'Grass or earth not graded or rolled',
                                'GVL' => 'Gravel',
                                'ICE' => 'Ice',
                                'LAT' => 'Laterite',
                                'MAC' => 'Macadam',
                                'PEM' => 'Partially concrete, asphalt or bitumen-bound macadam',
                                'PER' => 'Permanent surface, details unknown',
                                'PSP' => 'Marston Matting (derived from pierced/perforated steel planking)',
                                'SAN' => 'Sand',
                                'SMT' => 'Sommerfeld Tracking',
                                'SNO' => 'Snow',
                                'U' => 'Unknown surface',
                                'WAT' => 'Water',
                                'ROOF' => 'Rooftop (Heliport)'
                            ][ $runway['surface'] ] . '"></span>
                        </div>
                        <div class="lighting ' . ( $runway['lighted'] ? 'lighted' : '' ) . '">
                            <i class="icon">lightbulb</i>
                            <span data-i18n="' . ( $runway['lighted'] ? 'Lighted' : 'Not lighted' ) . '"></span>
                        </div>
                        <!--<div class="slope">
                            <i class="icon">north_east</i>
                            <span data-slope="' . base64_encode( json_encode( [
                                'from' => $runway['l_alt'],
                                'to' => $runway['r_alt'],
                                'length' => $runway['length']
                            ], JSON_NUMERIC_CHECK ) ) . '"></span>
                        </div>-->
                    </div>
                    ' . ( $runway['l_dthr'] ? '<div class="dthr">
                        <span>
                            <span data-i18n="Displaced threshold"></span>
                            <b>' . $runway[ 'l_ident' ] . '</b>
                            —
                            <b data-alt="' . $runway['l_dthr'] . '"></b>
                        </span>
                    </div>' : '' ) . '
                    ' . ( $runway['r_dthr'] ? '<div class="dthr">
                        <span>
                            <span data-i18n="Displaced threshold"></span>
                            <b>' . $runway[ 'r_ident' ] . '</b>
                            —
                            <b data-alt="' . $runway['r_dthr'] . '"></b>
                        </span>
                    </div>' : '' ) . '
                </div>
            </div>';

        }

        return '<div class="runwaylist">
            ' . $content . '
        </div>';

    }

    function regions_list(
        string $page,
        array $regions = []
    ) {

        foreach( $regions as $region ) {

            $content .= '<a data-href="' . $page . '/' . $region['code'] . '">
                <span data-i18n="' . ( $region['name'] ?? 'Unknown' ) . '"></span>
                <b>(<x data-number="' . $region['cnt'] . '"></x>)</b>
            </a>';

        }

        return '<div class="regionslist">
            ' . $content . '
        </div>';

    }

?>