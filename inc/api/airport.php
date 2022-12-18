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
    $airport = $airport->fetch_object();
    $ICAO = $airport->ICAO;

    $content = '<div class="breadcrumbs" data-bc="T' .
        $airport->continent . '::C' .
        $airport->country . '::R' .
        $airport->region . '::M' .
        $airport->municipality . '" data-bcdb="1"></div>
    <h1 class="primary-headline">
        <i class="icon">location_searching</i>
        <b>' . $ICAO . '</b>
        <span class="label">' . $airport->name . '</span>
    </h1>
    <div class="tablinks">
        <a class="tab" data-tab="info" data-href="airport/' . $ICAO . '/info">
            <i class="icon">location_on</i>
            <span data-i18n="Info"></span>
        </a>
        <a class="tab" data-tab="metar" data-href="airport/' . $ICAO . '/metar">
            <i class="icon">storm</i>
            <span data-i18n="Metar"></span>
        </a>
        <a class="tab" data-tab="forecast" data-href="airport/' . $ICAO . '/forecast">
            <i class="icon">grid_view</i>
            <span data-i18n="Forecast"></span>
        </a>
        <a class="tab" data-tab="nearby" data-href="airport/' . $ICAO . '/nearby">
            <i class="icon">near_me</i>
            <span data-i18n="Nearby"></span>
        </a>
        <a class="tab" data-tab="history" data-href="airport/' . $ICAO . '/history">
            <i class="icon">database</i>
            <span data-i18n="History"></span>
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
                ' . ( $airport->municipality ? '<div class="row">
                    <div class="label" data-i18n="Municipality"></div>
                    <div class="value">
                        <span data-i18n="' . $airport->municipality . '"></span>
                    </div>
                </div>' : '' ) . '
                ' . ( $airport->activation ? '<div class="row">
                    <div class="label" data-i18n="Date"></div>
                    <div class="value">
                        <span data-i18n="' . $airport->activation . '"></span>
                    </div>
                </div>' : '' ) . '
            </div>
            <div class="map" data-map="' . base64_encode( json_encode( [
                'lat' => $airport->lat,
                'lon' => $airport->lon,
                'zoom' => 14,
                'wheelZoom' => true
            ], JSON_NUMERIC_CHECK ) ) . '"></div>';

            break;

    }

    $content .= '</div>';

    echo json_encode( [
        'title' => $ICAO . ' — ' . $airport->name,
        'page' => 'airports',
        'content' => $content,
        'tab' => $tab
    ] );

?>