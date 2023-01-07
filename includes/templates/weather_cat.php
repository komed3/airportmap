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
<div class="weather">
    <?php _map( [
        'type' => 'weather',
        'navaids' => false,
        'supress_day_night' => true,
        'query' => [
            'flight_cat' => $cat
        ],
        'fit_bounds' => [
            [ $position->lat_min, $position->lon_min ],
            [ $position->lat_max, $position->lon_max ]
        ]
    ], 'minimal-ui windbug' ); ?>
    <h1 class="primary-headline cat-<?php echo $cat; ?>">
        <wxicon></wxicon>
        <b><?php echo $cat_name; ?></b>
        <span><?php echo $cat_label; ?></span>
    </h1>
    <div class="content-normal">
        <?php _back_to( 'weather', i18n( 'weather' ) ); ?>
    </div>
</div>
<?php _footer(); ?>