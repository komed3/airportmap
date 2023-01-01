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

        $weather = airport_weather( $airport );

        $infobox = [
            'image' => airport_image( $airport['ICAO'] ),
            'title' => $airport['ICAO'],
            'subtitle' => $airport['name'],
            'content' => '<ul class="infobox-list">
                <li>
                    <i class="icon">location_on</i>
                    <span>' . region_link( 'country', $airport['country'] ) . '</span>
                </li>
                <li>
                    <i class="icon">near_me</i>
                    ' . __DMS_coords( $airport['lat'], $airport['lon'] ) . '
                </li>
                <li>
                    <i class="icon">flight_takeoff</i>
                    <span>' . alt_in( (int) $airport['alt'] ) . '</span>
                    <span>(' . alt_in( (int) $airport['alt'] / 3.281, 'm&#8239;MSL' ) . ')</span>
                </li>
            </ul>',
            'link' => SITE . 'airport/' . $airport['ICAO'],
            'linktext' => i18n( 'view-airport' ),
            'classes' => 'cat-' . ( $weather['flight_cat'] ?? 'UNK' )
        ];

    }

    api_exit( [
        'raw' => $airport,
        'infobox' => $infobox ?? null
    ] );

?>