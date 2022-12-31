<?php

    require_once __DIR__ . '/api.php';

    if( !load_requirements( 'language', 'content', 'airport' ) ) {

        api_exit( [
            'raw' => null,
            'infobox' => null
        ] );

    }

    i18n_load( $_POST['locale'] ?? LOCALE );

    if( !empty( $airport = airport_by( 'ICAO', $_POST['airport'] ?? '' ) ) ) {

        $infobox = [
            'title' => '',
            'subtitle' => '',
            'content' => '',
            'link' => SITE . 'airport/' . $airport['ICAO'],
            'linktext' => i18n( 'view-airport' )
        ];

    }

    api_exit( [
        'raw' => $airport,
        'infobox' => $infobox ?? null
    ] );

?>