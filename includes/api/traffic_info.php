<?php

    require_once __DIR__ . '/api.php';

    if( !load_requirements( 'language', 'content', 'airport' ) ) {

        api_exit( [
            'raw' => null,
            'infobox' => null
        ] );

    }

    i18n_load( $_POST['locale'] ?? LOCALE );

    if( !empty( $state = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'traffic
        WHERE   ident = "' . $_POST['ident'] . '"
    ' ) ) ) {

        $state = $state->fetch_object();

        $infobox = [
            'image' => null,
            'title' => '...',
            'subtitle' => '...',
            'content' => '...',
            'classes' => 't-' . $state->type
        ];

    }

    api_exit( [
        'raw' => $state,
        'infobox' => $infobox ?? null
    ] );

?>