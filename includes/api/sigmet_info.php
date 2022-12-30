<?php

    require_once __DIR__ . '/api.php';

    if( !load_requirements( 'language', 'content' ) ) {

        api_exit( [
            'raw' => null,
            'infobox' => null
        ] );

    }

    i18n_load( $_POST['locale'] ?? LOCALE );

    $res = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'sigmet
        WHERE   _id = ' . $_POST['sigmet']
    );

    if( $res->num_rows == 1 && $sigmet = $res->fetch_object() ) {

        $infobox = [
            'title' => '',
            'subtitle' => '',
            'content' => '',
            'link' => SITE . 'weather/sigmets',
            'linktext' => i18n( 'view-sigmets' )
        ];

    }

    api_exit( [
        'raw' => $sigmet,
        'infobox' => $infobox ?? null
    ] );

?>