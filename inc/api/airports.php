<?php

    require_once __DIR__ . '/../apm.php';

    $code = strtoupper( trim( $_POST['code'] ?? '' ) );

    switch( strtolower( trim( $type = $_POST['type'] ?? 'world' ) ) ) {

        default:

            $page = 'continent';

            $list = $DB->query( '
                SELECT   c.code AS code,
                         c.name AS name,
                         COUNT( a.ICAO ) AS cnt
                FROM     ' . DB_PREFIX . 'continent c,
                         ' . DB_PREFIX . 'airport a
                WHERE    a.continent = c.code
                GROUP BY c.code
                ORDER BY c.code ASC
            ' )->fetch_all( MYSQLI_ASSOC );

            $title = 'Airports all over the world';

            $map = [
                'zoom' => 3,
                'minZoom' => 3,
                'lat' => 40,
                'lon' => 1
            ];

            $headline_label = 'Worldwide airports';
            $headline_count = array_sum( array_column( $list, 'cnt' ) );

            break;

        case 'continent':

            $continent = $DB->query( '
                SELECT  *
                FROM    ' . DB_PREFIX . 'continent
                WHERE   code = "' . $code . '"
            ' );

            if( $continent->num_rows != 1 ) {

                echo json_encode( [
                    'redirect_to' => 'airports'
                ] );

                exit;

            }

            $continent = $continent->fetch_object();

            $page = 'country';

            $list = $DB->query( '
                SELECT   c.code AS code,
                         c.name AS name,
                         COUNT( a.ICAO ) AS cnt
                FROM     ' . DB_PREFIX . 'country c,
                         ' . DB_PREFIX . 'airport a
                WHERE    c.continent = "' . $continent->code . '"
                AND      a.country = c.code
                GROUP BY c.code
                ORDER BY c.code ASC
            ' )->fetch_all( MYSQLI_ASSOC );

            $title = $continent->name;

            $map = [
                'zoom' => 4
            ];

            $headline_label = $title;
            $headline_count = array_sum( array_column( $list, 'cnt' ) );

            break;

    }

    echo json_encode( [
        'title' => $title,
        'page' => 'airports',
        'content' => '<div class="map halfsize" data-map="' . base64_encode( json_encode( $map, JSON_NUMERIC_CHECK ) ) . '"></div>
        <h1 class="primary-headline">
            <span class="label" data-i18n="' . $headline_label . '"></span>
            <b>(<x data-number="' . $headline_count . '"></x>)</b>
        </h1>
        ' . regions_list( $page, $list )
    ] );

?>