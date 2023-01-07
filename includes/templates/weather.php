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
    <div class="weather-extrema">
        <?php $i = 0; foreach( [
            'hot' => [ 'temp', 'temp DESC' ],
            'cold' => [ 'temp', 'temp ASC' ],
            'wind' => [ 'wind_spd', 'wind_spd DESC' ],
            'gust' => [ 'wind_gust', 'wind_gust DESC' ],
            'horiz' => [ 'vis_horiz', 'vis_horiz ASC' ],
            'vert' => [ 'vis_vert', 'vis_vert ASC' ],
            'precip' => [ 'precip', 'precip DESC' ]
        ] as $ext => $opt ) {

            $info = $DB->query( '
                SELECT   a.ICAO, a.name,
                         m.' . $opt[0] . ' AS col
                FROM     ' . DB_PREFIX . 'metar m,
                         ' . DB_PREFIX . 'airport a
                WHERE    m.station = a.ICAO
                AND      m.reported >= DATE_SUB( NOW(), INTERVAL 1 DAY )
                AND      m.' . $opt[0] . ' IS NOT NULL
                ORDER BY ' . $opt[1] . '
                LIMIT    0, 1
            ' );

            if( $info->num_rows == 0 || ++$i > 5 ) continue;

            $info = $info->fetch_assoc();

        ?>
            <div class="extrema">
                <i class="icon"><?php echo [
                    'hot' => 'sunny',
                    'cold' => 'ac_unit',
                    'wind' => 'air',
                    'gust' => 'cyclone',
                    'horiz' => 'cloudy',
                    'vert' => 'foggy',
                    'precip' => 'water_drop'
                ][ $ext ]; ?></i>
                <div class="info">
                    <div class="label"><?php _i18n( 'weather-extrema-' . $ext ); ?></div>
                    <div class="value"><?php

                        switch( $ext ) {

                            case 'hot':
                            case 'cold':
                                echo '<span>' . temp_in( round( $info['col'] ), 'c' ) . '</span><span>(' .
                                    temp_in( round( $info['col'] * 1.8 + 32 ), 'f' ) . ')</span>';
                                break;

                            case 'wind':
                            case 'gust':
                                echo '<span>' . wind_in( $info['col'], 'kt' ) . '</span><span>(' .
                                    wind_in( $info['col'] * 1.852, 'kmh' ) . ')</span>';
                                break;

                            case 'horiz':
                            case 'vert':
                                echo '<span></span>';
                                break;

                            case 'precip':
                                echo '<span></span>';
                                break;

                        }

                    ?></div>
                    <div class="station"><?php echo airport_link( $info ); ?></div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<?php _footer(); ?>