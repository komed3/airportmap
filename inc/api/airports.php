<?php

    require_once __DIR__ . '/../apm.php';

    $code = strtoupper( trim( $_POST['code'] ?? '' ) );

    switch( strtolower( trim( $type = $_POST['type'] ?? 'world' ) ) ) {

        default:

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

            $content = regions_list( 'continent', $list );

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

            $content = '<div class="breadcrumbs" data-bc="T' .
                $continent->code . '" data-bcdb="1"></div>
            ' . regions_list( 'country', $list );

            break;

        case 'country':

            $country = $DB->query( '
                SELECT  *
                FROM    ' . DB_PREFIX . 'country
                WHERE   code = "' . $code . '"
            ' );

            if( $country->num_rows != 1 ) {

                echo json_encode( [
                    'redirect_to' => 'airports'
                ] );

                exit;

            }

            $country = $country->fetch_object();

            $list = $DB->query( '
                SELECT   r.code AS code,
                         r.name AS name,
                         COUNT( a.ICAO ) AS cnt
                FROM     ' . DB_PREFIX . 'region r,
                         ' . DB_PREFIX . 'airport a
                WHERE    r.country = "' . $country->code . '"
                AND      a.region = r.code
                GROUP BY r.code
                ORDER BY r.code ASC
            ' )->fetch_all( MYSQLI_ASSOC );

            $title = $country->name ?? 'Unknown country';

            $map = [
                'zoom' => $country->zoom,
                'lat' => $country->lat,
                'lon' => $country->lon
            ];

            $headline_label = $title;
            $headline_count = array_sum( array_column( $list, 'cnt' ) );

            $content = '<div class="breadcrumbs" data-bc="T' .
                $country->continent . '::C' .
                $country->code . '" data-bcdb="1"></div>
            ' . regions_list( 'region', $list );

            break;

        case 'region':

            $region = $DB->query( '
                SELECT  *
                FROM    ' . DB_PREFIX . 'region
                WHERE   code = "' . $code . '"
            ' );

            if( $region->num_rows != 1 ) {

                echo json_encode( [
                    'redirect_to' => 'airports'
                ] );

                exit;

            }

            $region = $region->fetch_object();

            $country = $DB->query( '
                SELECT  *
                FROM    ' . DB_PREFIX . 'country
                WHERE   code = "' . $region->country . '"
            ' )->fetch_object();

            $airports = $DB->query( '
                SELECT   *
                FROM     ' . DB_PREFIX . 'airport
                WHERE    region = "' . $region->code . '"
                ORDER BY tier DESC
            ' )->fetch_all( MYSQLI_ASSOC );

            $title = $region->name ?? 'Unknown region';

            $map = [
                'zoom' => $country->zoom,
                'lat' => $country->lat,
                'lon' => $country->lon
            ];

            $headline_label = $title;
            $headline_count = count( $airports );

            $content = '<div class="breadcrumbs" data-bc="T' .
                $region->continent . '::C' .
                $region->country . '::R' .
                $region->code . '" data-bcdb="1"></div>
            ' . airport_list( $airports, $_POST['page'] ?? 1 );

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
        ' . $content
    ] );

?>