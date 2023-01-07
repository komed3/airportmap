<?php

    if( !in_array( $cat = strtoupper( $path[2] ?? '' ), [
        'VFR', 'MVFR', 'IFR', 'LIFR', 'UNK'
    ] ) ) {

        __404();

    }

    $position = $DB->query( '
        SELECT  MIN( a.lat ) AS lat_min,
                MIN( a.lon ) AS lon_min,
                MAX( a.lat ) AS lat_max,
                MAX( a.lon ) AS lon_max,
                AVG( a.lat ) AS lat_avg,
                AVG( a.lon ) AS lon_avg
        FROM    ' . DB_PREFIX . 'metar m,
                ' . DB_PREFIX . 'airport a
        WHERE   m.station = a.ICAO
        AND     m.reported >= DATE_SUB( NOW(), INTERVAL 1 DAY )
        AND     m.flight_cat = "' . $cat . '"
    ' )->fetch_object();

    $__site_canonical = $base . 'airports/weather/cat/' . $cat;

    $cat_name = i18n( 'cat-' . $cat );
    $cat_label = i18n( 'cat-' . $cat . '-label' );

    $__site_title = i18n( 'weather-cat-title', $cat_name, $cat_label );
    $__site_desc = i18n( 'weather-cat-desc', $cat_name, $cat_label );

    add_resource( 'weather', 'css', 'weather.css' );

    _header();

?>

<?php _footer(); ?>