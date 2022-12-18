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

    $airport = $airport->fetch_object();
    $ICAO = $airport->ICAO;

    $content = '<div class="breadcrumbs" data-bc="T' .
        $airport->continent . '::C' .
        $airport->country . '::R' .
        $airport->region . '::M' .
        $airport->municipality . '"></div>
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
    </div>';

    echo json_encode( [
        'title' => $ICAO . ' — ' . $airport->name,
        'page' => 'airports',
        'content' => $content,
        'tab' => $_POST['tab'] ?? 'info'
    ] );

?>