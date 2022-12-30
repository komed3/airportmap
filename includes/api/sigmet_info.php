<?php

    require_once __DIR__ . '/api.php';

    if( !load_requirements( 'language', 'content', 'weather' ) ) {

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

    if( $res->num_rows == 1 && $sigmet = $res->fetch_assoc() ) {

        $infobox = [
            'title' => sigmet_hazard( $sigmet ),
            'subtitle' => $sigmet['name'],
            'content' => '<div class="rawtxt">' . $sigmet['raw'] . '</div>
            <h5>' . i18n( 'valid-from-to',
                date( 'H:i', strtotime( $sigmet['valid_from'] ) ),
                date( 'H:i', strtotime( $sigmet['valid_to'] ) )
            ) . '</h5>',
            'link' => SITE . 'weather/sigmets',
            'linktext' => i18n( 'view-sigmets' )
        ];

    }

    api_exit( [
        'raw' => $sigmet,
        'infobox' => $infobox ?? null
    ] );

?>