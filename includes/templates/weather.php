<?php

    $cat_stats = flight_cat_count();

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
    ' )->fetch_object();

    $__site_canonical = $base . 'airports/weather';

    $__site_title = i18n( 'weather-title' );
    $__site_desc = i18n( 'weather-desc' );

    add_resource( 'weather', 'css', 'weather.css' );

    _header();

?>
<div class="weather">
    <?php _map( [
        'type' => 'weather',
        'navaids' => false,
        'supress_day_night' => true,
        'fit_bounds' => [
            [ $position->lat_min, $position->lon_min ],
            [ $position->lat_max, $position->lon_max ]
        ]
    ], 'minimal-ui windbug' ); ?>
    <h1 class="primary-headline">
        <i class="icon">sunny</i>
        <span><?php _i18n( 'weather' ); ?></span>
    </h1>
    <div class="cat-stats content-normal">
        <div class="label"><?php _i18n( 'flight-cats' ); ?></div>
        <?php foreach( [ 'VFR', 'MVFR', 'IFR', 'LIFR', 'UNK' ] as $cat ) { ?>
            <a href="<?php _base_url( 'weather/cat/' . $cat ); ?>" class="cat cat-<?php echo $cat; ?>">
                <wxicon></wxicon>
                <span><?php echo $cat; ?></span>
                <b><?php echo __number( $cat_stats[ $cat ] ); ?></b>
            </a>
        <?php } ?>
    </div>
</div>
<?php _footer(); ?>