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
            'title' => $state->callsign ?? i18n( 'unknown' ),
            'subtitle' => i18n( 'squawk-code', $state->squawk ?? i18n( 'unknown' ) ),
            'content' => '<ul class="infobox-list">
                <li>
                    <i class="icon">flight</i>
                    <span>' . i18n( 'traffic-type-' . $state->type ) . '</span>
                </li>
                <li>
                    <i class="icon">location_on</i>
                    ' . __DMS_coords( $state->lat, $state->lon ) . '
                </li>
                <li>
                    <i class="icon">update</i>
                    <span>' . i18n( 'traffic-time',
                        date( i18n( 'clock-time' ), strtotime( $state->contact ) )
                    ) . '</span>
                </li>
                <li>
                    <i class="icon">cell_tower</i>
                    <span>' . i18n( 'traffic-source-' . $state->source ) . '</span>
                </li>
            </ul>
            <hr />
            <ul class="infobox-list">
                <li>
                    <i class="icon">flight_takeoff</i>
                    ' . ( $state->ground
                        ? '<span>' . i18n( 'on-ground' ) . '</span>'
                        : '<b>FL' . str_pad( floor( $state->alt / 100 ), 3, '0', STR_PAD_LEFT ) . '</b>
                           <span>' . alt_in( $state->alt, 'ft' ) . '</span>'
                    ) . '
                </li>
                ' . ( $state->vrate == 0 ? '' : '<li>
                    <i class="icon">swap_vert</i>
                    <b>VS' . ( $state->vrate < 0 ? '–' : '+' ) . round( abs( $state->vrate ) ) . '</b>
                    <span>' . alt_in( $state->vrate, 'ft/min' ) . '</span>
                </li>' ) . '
                <li>
                    <i class="icon">near_me</i>
                    <span>' . round( $state->hdg ) . '°</span>
                    <b>' . __cardinal( $state->hdg ) . '</b>
                </li>
                <li>
                    <i class="icon">speed</i>
                    <b>' . __number( $state->velocity ) . 'kn</b>
                    <span>' . i18n( 'mach', __number( __mach(
                        $state->velocity ?? 0,
                        $state->alt ?? 0
                    ), 2 ) ) . '</span>
                </li>
            </ul>',
            'classes' => 't-' . $state->type . ( $state->ground ? ' ground' : '' )
        ];

    }

    api_exit( [
        'raw' => $state,
        'infobox' => $infobox ?? null
    ] );

?>