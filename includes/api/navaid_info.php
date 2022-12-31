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
            'title' => format_freq( $navaid['frequency'] ),
            'subtitle' => $navaid['type'] . ' ' . $navaid['ident'],
            'content' => '<ul class="infobox-list">
                ' . ( $navaid['country'] ? '<li>
                    <i class="icon">location_on</i>
                    <span>' . region_name( 'country', $navaid['country'] ) . '</span>
                </li>' : '' ) . '
                ' . ( $navaid['level'] ? '<li>
                    <i class="icon">near_me</i>
                    <span>' . navaid_level( $navaid ) . '</span>
                </li>' : '' ) . '
            </ul>'
        ];

    }

    api_exit( [
        'raw' => $navaid,
        'infobox' => $infobox ?? null
    ] );

?>