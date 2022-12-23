<?php

    require_once __DIR__ . '/../apm.php';

    $airport = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'airport
        WHERE   ICAO = "' . strtoupper( trim( $_POST['airport'] ?? '' ) ) . '"
    ' );

    if( $airport->num_rows == 0 ) {

        echo json_encode( [
            'redirect_to' => ''
        ] );

        exit;

    }

    $tab = $_POST['tab'] ?? 'info';
    $subtab = $_POST['subtab'] ?? '';
    $airport = $airport->fetch_object();
    $ICAO = $airport->ICAO;
    $base = 'airport/' . $ICAO;

    $content = '<div class="breadcrumbs no-world" data-bc="T' .
        $airport->continent . '::C' .
        $airport->country . '::R' .
        $airport->region . '::M' .
        $airport->municipality . '" data-bcdb="1"></div>
    <h1 class="primary-headline type-' . $airport->type . ' restrict-' . $airport->restriction . '">
        <navicon class="invert"></navicon>
        <b>' . $ICAO . '</b>
        <span class="label">' . $airport->name . '</span>
    </h1>
    <div class="tablinks">
        <a class="tab" data-tab="info" data-href="' . $base . '/info">
            <i class="icon">location_on</i>
            <span data-i18n="Info"></span>
        </a>
        <a class="tab" data-tab="metar" data-href="' . $base . '/metar">
            <i class="icon">cloud</i>
            <span data-i18n="METAR"></span>
        </a>
        <a class="tab" data-tab="taf" data-href="' . $base . '/taf">
            <i class="icon">storm</i>
            <span data-i18n="TAF"></span>
        </a>
        <a class="tab" data-tab="nearby" data-href="' . $base . '/nearby">
            <i class="icon">near_me</i>
            <span data-i18n="Nearby"></span>
        </a>
        <a class="tab" data-tab="radio" data-href="' . $base . '/radio">
            <i class="icon">radar</i>
            <span data-i18n="Radio"></span>
        </a>
        <a class="tab" data-tab="runway" data-href="' . $base . '/runway">
            <i class="icon">flight_takeoff</i>
            <span data-i18n="Runway"></span>
        </a>
        <span class="space"></span>
        ' . ( $airport->home ? '<a class="tab" href="' . $airport->home . '" target="_blank">
            <i class="icon">language</i>
            <span data-i18n="Website"></span>
        </a>' : '' ) . '
        ' . ( $airport->wiki ? '<a class="tab" href="https://' .
            str_replace( ':', '.wikipedia.org/wiki/', $airport->wiki ) . '" target="_blank">
            <i class="icon">school</i>
            <span data-i18n="Wiki"></span>
        </a>' : '' ) . '
    </div>
    <div class="tabcontent tab-' . $tab . '">';

    switch( $tab ) {

        case 'info':

            $content .= '<div class="infolist">
                ' . ( $airport->IATA ? '<div class="row">
                    <div class="label" data-i18n="IATA-Code"></div>
                    <div class="value">
                        <span>' . $airport->IATA . '</span>
                    </div>
                </div>' : '' ) . '
                ' . ( $airport->GPS ? '<div class="row">
                    <div class="label" data-i18n="GPS-Code"></div>
                    <div class="value">
                        <span>' . $airport->GPS . '</span>
                    </div>
                </div>' : '' ) . '
                <div class="row">
                    <div class="label" data-i18n="Facility type"></div>
                    <div class="value">
                        <a data-href="type/' . $airport->type . '" data-i18n="' . [
                            'large' => 'International Airport',
                            'medium' => 'Airport',
                            'small' => 'Airfield',
                            'heliport' => 'Heliport',
                            'altiport' => 'Altiport',
                            'seaplane' => 'Seaplane Base',
                            'balloonport' => 'Balloon Port',
                            'closed' => 'Closed'
                        ][ $airport->type ] . '"></a>
                    </div>
                </div>
                ' . ( $airport->restriction ? '<div class="row">
                    <div class="label" data-i18n="Restriction"></div>
                    <div class="value">
                        <a data-href="restriction/' . $airport->restriction . '" data-i18n="' .
                            ucfirst( $airport->restriction ) . '"></a>
                    </div>
                </div>' : '' ) . '
                <div class="row">
                    <div class="label" data-i18n="Coordinates"></div>
                    <div class="value">
                        <span class="coord lat" data-lat="' . $airport->lat . '"></span>
                        <span class="coord lon" data-lon="' . $airport->lon . '"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="label" data-i18n="Elevation"></div>
                    <div class="value">
                        <span class="alt ft" data-alt="' . $airport->alt . '"></span>
                        <span class="alt msl" data-msl="' . $airport->alt . '"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="label" data-i18n="Airline Service"></div>
                    <div class="value">
                        <span data-i18n="' . ( $airport->service ? 'Yes' : 'No' ) . '"></span>
                    </div>
                </div>
                ' . ( $airport->timezone ? '<div class="row">
                    <div class="label" data-i18n="Timezone"></div>
                    <div class="value">
                        <a data-href="tz/' . $airport->timezone . '">
                            <span data-i18n="' . $airport->timezone . '"></span>
                            <span>(GMT' . str_replace( '+-', '-', '+' .
                                round( $airport->offset / 60, 1 ) ) . ')</span>
                        </a>
                    </div>
                </div>' : '' ) . '
            </div>
            <div class="map" data-map="' . base64_encode( json_encode( [
                'lat' => $airport->lat,
                'lon' => $airport->lon,
                'zoom' => 14,
                'wheelZoom' => true,
                'divider' => 2
            ], JSON_NUMERIC_CHECK ) ) . '"></div>';

            break;

        case 'metar':

            if( empty( $reports = airport_weather(
                'metar',
                $airport->lat,
                $airport->lon
            ) ) ) {

                $content .= '<p data-i18n="No metar data matched for this record."></p>';

            } else {

                $station = (int) array_search(
                    $subtab = strtoupper( $subtab ),
                    $stations = array_column( $reports, 'station' )
                );

                $stationID = $stations[ $station ];

                $metar = $reports[ $station ];

                $content .= '<div class="metarselect">
                    <select data-action="select-station" data-base="' . $base . '/metar/">
                        ' . implode( '', array_map( function( $s ) use ( $stationID ) {
                            return '<option value="' . $s . '" ' . (
                                $s == $stationID ? 'selected' : ''
                            ) . '>' . $s . '</option>';
                        }, $stations ) ) . '
                    </select>
                    <div class="station">
                        <a data-href="airport/' . $metar['ICAO'] . '">' . $metar['name'] . '</a>
                    </div>
                    <div class="datetime">
                        <span data-localtime="' . $metar['reported'] . '" data-offset="' . $metar['offset'] . '"></span>
                    </div>
                    ' . ( $metar['distance'] > 0 ? '<div class="distance dist-' . min( 3, floor( $metar['distance'] / 25 ) ) . '">
                        <i class="icon">near_me</i>
                        <span data-nm="' . $metar['distance'] . '"></span>
                    </div>' : '' ) . '
                    <div class="ago" data-ago="' . $metar['reported'] . '">
                        <i class="icon">schedule</i>
                        <span class="ago-echo"></span>
                    </div>
                </div>
                <div class="metarcheck">
                    <div class="metarbox cat cat-' . $metar['flight_cat'] . '">
                        <div class="top">
                            <span data-i18n="' . $metar['flight_cat'] . '"></span>
                        </div>
                        <div class="bot">
                            <span class="label" data-i18n="Flight cat"></span>
                        </div>
                    </div>
                    <div class="metarbox weather" data-wx="' . $metar['wx'] . '" data-vert="' .
                            $metar['vis_vert'] . '" data-cover="' . $metar['cloud_1_cover'] . '">
                        <div class="top">
                            <i class="icon"></i>
                            <span data-temp="' . $metar['temp'] . '"></span></div>
                        <div class="bot">
                            <span class="label"></span>
                        </div>
                    </div>
                    <div class="metarbox wind str-' . min( 3, floor( $metar['wind_spd'] / 10 ) ) . '" data-hdg="' .
                            $metar['wind_dir'] . '">
                        <div class="top">
                            <div class="bug">
                                <i class="icon">navigation</i>
                            </div>
                            <span data-kt="' . $metar['wind_spd'] . '"></span>
                        </div>
                        <div class="bot">
                            <span class="label" data-i18n="Wind"></span>
                            <b class="cardinal"></b>
                            <span class="deg"></span>
                        </div>
                    </div>
                    <div class="metarbox visibility vis-' . min( 3, floor( $metar['vis_horiz'] / 2 ) ) . '">
                        <div class="top">
                            <b data-mi="' . $metar['vis_horiz'] . '"></b>
                        </div>
                        <div class="bot">
                            <span class="label" data-i18n="Visibility"></span>
                        </div>
                    </div>
                    <div class="metarbox ceiling vis-' . min( 3, floor( ( $metar['vis_vert'] ?? 12000 ) / 1000 ) ) . '">
                        <div class="top">
                            ' . ( $metar['vis_vert'] == null
                                ? '<span data-i18n="Clear"></span>'
                                : '<b data-alt="' . $metar['vis_vert'] . '"></b>' ) . '
                        </div>
                        <div class="bot">
                            <span class="label" data-i18n="Ceiling"></span>
                        </div>
                    </div>
                    <div class="metarbox altim">
                        <div class="top">
                            <span data-altim-hpa="' . ( $metar['altim'] * 33.864 ) . '"></span>
                        </div>
                        <div class="bot">
                            <span class="label" data-i18n="Altimeter"></span>
                        </div>
                    </div>
                </div>
                <div class="rawtext">' . $metar['raw'] . '</div>';

            }

            break;

        case 'nearby':

            if( empty( $subtab ) ) $subtab = 'nearest';

            $content .= '<div class="map" data-map="' . base64_encode( json_encode( [
                'lat' => $airport->lat,
                'lon' => $airport->lon,
                'zoom' => 10,
                'wheelZoom' => true,
                'divider' => 2
            ], JSON_NUMERIC_CHECK ) ) . '"></div>
            <div class="tablinks">
                <span class="space"></span>
                <a class="tab" data-subtab="nearest" data-href="' . $base . '/nearby/nearest">
                    <span data-i18n="Nearest"></span>
                </a>
                <a class="tab" data-subtab="biggest" data-href="' . $base . '/nearby/biggest">
                    <span data-i18n="Biggest"></span>
                </a>
                <a class="tab" data-subtab="airports" data-href="' . $base . '/nearby/airports">
                    <span data-i18n="Airports"></span>
                </a>
                <a class="tab" data-subtab="service" data-href="' . $base . '/nearby/service">
                    <span data-i18n="Airline Service"></span>
                </a>
                <a class="tab" data-subtab="all" data-href="' . $base . '/nearby/all">
                    <span data-i18n="All"></span>
                </a>
                <span class="space"></span>
            </div>
            ' . airport_list( airport_nearest(
                $airport->lat,
                $airport->lon,
                $ICAO,
                [
                    'nearest' => [ 'closed' ],
                    'biggest' => [ 'closed', 'balloonport', 'heliport', 'seaplane', 'small', 'medium' ],
                    'airports' => [ 'closed', 'balloonport', 'heliport', 'seaplane' ],
                    'service' => [ 'closed', 'balloonport', 'heliport', 'seaplane' ],
                    'all' => []
                ][ $subtab ],
                $subtab == 'service'
            ), -1, [
                $airport->lat,
                $airport->lon
            ] );

            break;

        case 'radio':

            $content .= '<h2 class="secondary-headline" data-i18n="Airport Frequencies"></h2>';

            if( count( $radios = $DB->query( '
                SELECT  *
                FROM    ' . DB_PREFIX . 'frequency
                WHERE   airport = "' . $ICAO . '"
            ' )->fetch_all( MYSQLI_ASSOC ) ) > 0 ) {

                $content .= radio_list( $radios );

            } else {

                $content .= '<p data-i18n="No frequencies matched for this record."></p>';

            }
            
            if( count( $navaids = $DB->query( '
                SELECT  *
                FROM    ' . DB_PREFIX . 'navaid
                WHERE   airport = "' . $ICAO . '"
            ' )->fetch_all( MYSQLI_ASSOC ) ) > 0 ) {

                $content .= '<h2 class="secondary-headline" data-i18n="Navaids"></h2>
                ' . navaid_list( $navaids );

            }

            break;

        case 'runway':

            if( count( $runways = $DB->query( '
                SELECT  *
                FROM    ' . DB_PREFIX . 'runway
                WHERE   airport = "' . $ICAO . '"
            ' )->fetch_all( MYSQLI_ASSOC ) ) > 0 ) {

                $content .= runway_list( $runways );

            } else {

                $content .= '<p data-i18n="No runways matched for this record."></p>';

            }

            break;


    }

    $content .= '</div>';

    echo json_encode( [
        'title' => $ICAO . ' — ' . $airport->name,
        'page' => 'airports',
        'content' => $content,
        'tab' => $tab,
        'subtab' => $subtab
    ] );

?>