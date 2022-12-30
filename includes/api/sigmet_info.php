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

        $valid_from = strtotime( $sigmet['valid_from'] );
        $valid_to = strtotime( $sigmet['valid_to'] );

        $fl_msgkey = 'fl';

        if( !empty( $fl_base = $sigmet['lo_1'] ?? ( $sigmet['lo_2'] ?? null ) ) )
            $fl_msgkey .= '-from';

        if( !empty( $fl_top = $sigmet['hi_1'] ?? ( $sigmet['hi_2'] ?? null ) ) )
            $fl_msgkey .= '-to';

        $infobox = [
            'title' => sigmet_hazard( $sigmet ),
            'subtitle' => $sigmet['name'],
            'content' => '<div class="rawtxt">' . $sigmet['raw'] . '</div>
            <hr />
            <h5>
                <i class="icon">schedule</i>
                <span>' . sigmet_valid( $sigmet ) . '</span>
            </h5>
            <h5>
                <i class="icon">near_me</i>
                <span>' . sigmet_move( $sigmet ) . '</span>
            </h5>
            <h5>
                <i class="icon">warning</i>
                <span>' . sigmet_cng( $sigmet ) . '</span>
            </h5>
            ' . ( empty( $fl = sigmet_fl( $sigmet ) ) ? '' : '<h5>
                <i class="icon">flight_takeoff</i>
                <span>' . $fl . '</span>
            </h5>' ),
            'link' => SITE . 'weather/sigmets',
            'linktext' => i18n( 'view-sigmets' )
        ];

    }

    api_exit( [
        'raw' => $sigmet,
        'infobox' => $infobox ?? null
    ] );

?>