<?php

    require_once __DIR__ . '/api.php';

    if( !load_requirements( 'language', 'content', 'airport', 'navaid' ) ) {

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
            'title' => $freq = format_freq( $navaid['frequency'] ),
            'subtitle' => $navaid['type'] . ' ' . $navaid['name'],
            'content' => '<ul class="infobox-list">
                <li>
                    <i class="icon">tag</i>
                    <span>' . $navaid['type'] . ' <b>' . $navaid['ident'] . '</b></span>
                </li>
                <li>
                    <i class="icon">radar</i>
                    <span>' . $freq . '</span>
                </li>
                ' . ( $navaid['country'] ? '<li>
                    <i class="icon">location_on</i>
                    <span>' . region_link( 'country', $navaid['country'] ) . '</span>
                </li>' : '' ) . '
                ' . ( !empty( $airport = airport_by( 'ICAO', $navaid['airport'] ?? '' ) ) ? '<li>
                    <i class="icon">flight</i>
                    <span>' . airport_link( $airport ) . '</span>
                </li>' : '' ) . '
                ' . ( $navaid['level'] ? '<li>
                    <i class="icon">cell_tower</i>
                    <span>' . navaid_level( $navaid ) . '</span>
                </li>' : '' ) . '
                ' . ( $navaid['power'] ? '<li>
                    <i class="icon">power</i>
                    <span>' . navaid_power( $navaid ) . '</span>
                </li>' : '' ) . '
                <li>
                    <i class="icon">near_me</i>
                    ' . __DMS_coords( $navaid['lat'], $navaid['lon'] ) . '
                </li>
                ' . ( $navaid['alt'] ? '<li>
                    <i class="icon">flight_takeoff</i>
                    <span>' . alt_in( $navaid['alt'] ) . '</span>
                    <span>(' . alt_in( $navaid['alt'] / 3.281, 'm&nbsp;MSL' ) . ')</span>
                </li>' : '' ) . '
            </ul>'
        ];

    }

    api_exit( [
        'raw' => $navaid,
        'infobox' => $infobox ?? null
    ] );

?>