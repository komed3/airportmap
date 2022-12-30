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
            <hr />
            <ul class="infobox-list">
                <li>
                    <i class="icon">schedule</i>
                    <span>' . sigmet_valid( $sigmet ) . '</span>
                </li>
                <li>
                    <i class="icon">near_me</i>
                    <span>' . sigmet_move( $sigmet ) . '</span>
                </li>
                <li>
                    <i class="icon">warning</i>
                    <span>' . sigmet_cng( $sigmet ) . '</span>
                </li>
                ' . ( empty( $fl = sigmet_fl( $sigmet ) ) ? '' : '<li>
                    <i class="icon">flight_takeoff</i>
                    <span>' . $fl . '</span>
                </li>' ) . '
            </ul>',
            'link' => SITE . 'weather/sigmets',
            'linktext' => i18n( 'view-sigmets' )
        ];

    }

    api_exit( [
        'raw' => $sigmet,
        'infobox' => $infobox ?? null
    ] );

?>