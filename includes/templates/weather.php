<?php

    $cat_stats = flight_cat_count();

    $stations = $DB->query( '
        SELECT   *
        FROM     ' . DB_PREFIX . 'metar m,
                 ' . DB_PREFIX . 'airport a
        WHERE    m.station = a.ICAO
        AND      m.reported >= DATE_SUB( NOW(), INTERVAL 1 DAY )
        ORDER BY tier DESC
    ' )->fetch_all( MYSQLI_ASSOC );

    $__site_canonical = 'airports/weather';

    $__site_title = i18n( 'weather-title' );
    $__site_desc = i18n( 'weather-desc' );

    add_resource( 'weather', 'css', 'weather.css' );

    _header();

?>
<div class="weather">
    <?php _map( [
        'type' => 'weather',
        'navaids' => false,
        'waypoints' => false,
        'supress_day_night' => true,
        'position' => [
            'lat' => 40.7,
            'lon' => -74,
            'zoom' => 5
        ]
    ], 'minimal-ui windbug' ); ?>
    <h1 class="primary-headline">
        <i class="icon">sunny</i>
        <span><?php _i18n( 'weather' ); ?></span>
    </h1>
    <div class="cat-stats content-normal">
        <div class="label"><?php _i18n( 'flight-cats' ); ?></div>
        <?php foreach( [ 'VFR', 'MVFR', 'IFR', 'LIFR', 'UNK' ] as $cat ) { ?>
            <a href="<?php _base_url( 'weather/cat/' . $cat ); ?>" class="cat cat-<?php echo $cat; ?>" title="<?php _i18n( 'cat-' . $cat . '-label' ); ?>">
                <wxicon></wxicon>
                <span><?php _i18n( 'cat-' . $cat ); ?></span>
                <b><?php echo __number( $cat_stats[ $cat ] ); ?></b>
            </a>
        <?php } ?>
    </div>
    <div class="weather-extrema content-normal">
        <?php foreach( [
            'vert' => [ 'vis_vert', 'vis_vert ASC' ],
            'hot' => [ 'temp', 'temp DESC' ],
            'cold' => [ 'temp', 'temp ASC' ],
            'wind' => [ 'wind_spd', 'wind_spd DESC' ],
            'gust' => [ 'wind_gust', 'wind_gust DESC' ],
            'horiz' => [ 'vis_horiz', 'vis_horiz ASC' ]
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

            if( $info->num_rows == 0 ) continue;

            $info = $info->fetch_assoc();

        ?>
            <div class="extrema">
                <i class="icon"><?php echo [
                    'vert' => 'foggy',
                    'hot' => 'sunny',
                    'cold' => 'ac_unit',
                    'wind' => 'air',
                    'gust' => 'cyclone',
                    'horiz' => 'cloudy'
                ][ $ext ]; ?></i>
                <div class="info">
                    <div class="label"><?php _i18n( 'weather-extrema-' . $ext ); ?></div>
                    <div class="value"><?php

                        switch( $ext ) {

                            case 'vert':
                                echo '<span>' . alt_in( $info['col'], 'ft' ) . '</span>';
                                break;

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
                                echo '<span>' . alt_in( $info['col'] * 1609.34, 'm' ) . '</span>';
                                break;

                        }

                    ?></div>
                    <div class="station"><?php echo airport_link( $info ); ?></div>
                </div>
            </div>
        <?php } ?>
    </div>
    <a href="<?php _base_url( 'weather/sigmets' ); ?>" class="weather-active-sigmets content-normal">
        <span><?php _i18n( 'sigmets-title', __number( $DB->query( '
            SELECT  _id
            FROM    ' . DB_PREFIX . 'sigmet
            WHERE   valid_from <= NOW()
            AND     valid_to >= NOW()
        ' )->num_rows ) ); ?></span>
        <i class="icon">chevron_right</i>
    </a>
    <div class="content-normal">
        <h2 class="secondary-headline"><?php _i18n( 'weather-title' ); ?></h2>
        <?php _station_list( $stations, $path[1] ?? 1, 'weather' ); ?>
    </div>
</div>
<?php _footer(); ?>