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
        FROM    ' . DB_PREFIX . 'navaid
        WHERE   _id = ' . $_POST['navaid']
    );

    if( $res->num_rows == 1 && $navaid = $res->fetch_assoc() ) {

        $infobox = [
            'title' => '',
            'subtitle' => '',
            'content' => ''
        ];

    }

    api_exit( [
        'raw' => $navaid,
        'infobox' => $infobox ?? null
    ] );

?>