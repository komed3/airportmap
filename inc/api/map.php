<?php

    require_once __DIR__ . '/../apm.php';

    $stats = airport_stats();

    $content = '<div class="map" data-map="' . base64_encode( json_encode( [
        'zoom' => 8
    ], JSON_NUMERIC_CHECK ) ) . '"></div>
    <div class="welcome">
        <h1 data-i18n="Welcome to <b>Airportmap</b>"></h1>
        <p data-i18n="Airportmap is an open source project with information and weather data on more than 60,000 airports, heliports and air bases worldwide."></p>
    </div>
    <form data-form="search" class="fullsearch" autocomplete="off">
        <input type="text" name="searchtext" data-placeholder="Search for ICAO, IATA or airport name …" />
        <button type="submit" name="searchsubmit">
            <i class="icon">travel_explore</i>
        </button>
    </form>
    <div class="bigstats">
        <div class="column">
            <h2 data-number="' . $stats['large'] + $stats['medium'] . '"></h2>
            <span data-i18n="Airports"></span>
        </div>
        <div class="column">
            <h2 data-number="' . $stats['small'] . '"></h2>
            <span data-i18n="Airfields"></span>
        </div>
        <div class="column">
            <h2 data-number="' . $stats['heliport'] . '"></h2>
            <span data-i18n="Heliports"></span>
        </div>
        <div class="column">
            <h2 data-number="' . $stats['seaplane'] . '"></h2>
            <span data-i18n="Seaplanes"></span>
        </div>
    </div>';

    echo json_encode( [
        'title' => 'Discover airports all over the world',
        'content' => $content,
        'page' => 'map',
        'styles' => [ 'map' ]
    ] );

?>