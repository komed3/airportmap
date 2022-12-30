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

        $infobox = [
            'title' => sigmet_hazard( $sigmet ),
            'subtitle' => $sigmet['name'],
            'content' => '<div class="rawtxt">' . $sigmet['raw'] . '</div>
            <hr />
            <h5>
                <i class="icon">schedule</i>
                <span>' . i18n( $valid_to >= strtotime( 'tomorrow' )
                    ? 'valid-until' : 'valid-from-to',
                date( 'm/d', $valid_from ),
                date( 'H:i', $valid_from ),
                date( 'm/d', $valid_to ),
                date( 'H:i', $valid_to )
                ) . '</span>
            </h5>
            <h5>
                <i class="icon">near_me</i>
                <span>' . ( $sigmet['dir'] ? i18n( 'movement',
                    i18n( 'dir-' . $sigmet['dir'] ),
                    (int) $sigmet['spd'] . '&#8239;kt'
                ) : i18n( 'stationary' ) ) . '</span>
            </h5>
            <h5>
                <i class="icon">warning</i>
                <span>' . ucfirst( i18n( 'change-' . ( $sigmet['cng'] ?? 'NC' ) ) ) . '</span>
            </h5>',
            'link' => SITE . 'weather/sigmets',
            'linktext' => i18n( 'view-sigmets' )
        ];

    }

    api_exit( [
        'raw' => $sigmet,
        'infobox' => $infobox ?? null
    ] );

?>