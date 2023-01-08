<?php

    global $DB, $path, $base, $airport;

    if( empty( $airport ) ) {

        __404();

    }

    $stations = $DB->query( '
        SELECT   *, ( 3440.29182 * acos(
            cos( radians( ' . $airport['lat'] . ' ) ) *
            cos( radians( a.lat ) ) *
            cos(
                radians( a.lon ) -
                radians( ' . $airport['lon'] . ' )
            ) +
            sin( radians( ' . $airport['lat'] . ' ) ) *
            sin( radians( a.lat ) )
        ) ) AS distance, TIMESTAMPDIFF(
            MINUTE, m.reported, "' . date( 'Y-m-d H:i:s' ) . '"
        ) AS age
        FROM     ' . DB_PREFIX . 'metar m,
                 ' . DB_PREFIX . 'airport a
        WHERE    m.station = a.ICAO
        AND      (
            a.lat BETWEEN ' . ( $airport['lat'] - 15 ) . '
            AND ' . ( $airport['lat'] + 15 ) . '
        )
        AND      (
            a.lon BETWEEN ' . ( $airport['lon'] - 15 ) . '
            AND ' . ( $airport['lon'] + 15 ) . '
        )
        AND      m.reported >= DATE_SUB( NOW(), INTERVAL 1 DAY )
        ORDER BY ( distance + age ) ASC
        LIMIT    0, 10
    ' )->fetch_all( MYSQLI_ASSOC );

?>
<div class="airport-weather content-normal">
    <?php if( empty( $stations ) ) { ?>
        <p><?php _i18n( 'airport-weather-empty' ); ?></p>
    <?php } else {

        $index = (int) array_search(
            strtoupper( $path[3] ?? 0 ),
            array_column( $stations, 'ICAO' )
        );

        $weather = $stations[ $index ];

        $windchill = windchill( $weather );

    ?>
        <div class="weather-station <?php echo $weather['distance'] < 1 ? 'on-site' : ''; ?>">
            <div class="label"><?php _i18n( 'airport-weather-station' ); ?></div>
            <select data-action="select-station" data-base="<?php echo $base; ?>weather/">
                <?php foreach( $stations as $idx => $station ) { ?>
                    <option value="<?php echo $station['ICAO']; ?>" <?php echo $idx == $index ? 'selected': ''; ?>>
                        <?php _i18n( 'airport-weather-select', $station['ICAO'], $station['name'] ); ?>
                    </option>
                <?php } ?>
            </select>
            <div class="space"></div>
            <div class="quality q-<?php echo min( 3, floor( $weather['distance'] / 50 ) ); ?> dist">
                <i class="icon">near_me</i>
                <span><?php echo __number( $weather['distance'] ); ?>&nbsp;nm</span>
            </div>
            <div class="quality q-<?php echo min( 3, floor( $weather['age'] / 60 ) ); ?> age">
                <i class="icon">schedule</i>
                <span><?php echo __timediff( $weather['age'] ); ?></span>
            </div>
        </div>
        <div class="weather-info">
            <div class="info">
                <div class="icon">
                    <?php echo wx_icon( $weather ); ?>
                </div>
                <div class="list">
                    <div class="row temp">
                        <b><?php echo temp_in( $weather['temp'], 'c' ); ?></b>
                        <span>(<?php echo temp_in( $weather['temp'] * 1.8 + 32, 'f' ); ?>)</span>
                    </div>
                    <div class="row wx">
                        <span><?php echo ucfirst( wx( $weather ) ); ?></span>
                    </div>
                    <div class="row wind">
                        <span><?php echo wind_info( $weather, true ); ?></span>
                    </div>
                </div>
            </div>
            <hr />
            <ul class="list">
                <li>
                    <span class="label"><?php _i18n( 'weather-altimeter' ); ?></span>
                    <div>
                        <b><?php echo altim_in( $weather['altim'], 'inhg', 2 ); ?></b>
                        <span>(<?php echo altim_in( $weather['altim'] * 33.863886, 'hpa', 0 ); ?>)</span>
                    </div>
                </li>
                <li>
                    <span class="label"><?php _i18n( 'weather-dewpoint' ); ?></span>
                    <div>
                        <b><?php echo temp_in( $weather['dewp'], 'c' ); ?></b>
                        <span>(<?php echo temp_in( $weather['dewp'] * 1.8 + 32, 'f' ); ?>)</span>
                    </div>
                </li>
                <li>
                    <span class="label"><?php _i18n( 'weather-relhum' ); ?></span>
                    <div>
                        <b><x><?php echo __number( relhum( $weather ), 1 ); ?></x>&#8239;%</b>
                    </div>
                </li>
                <li>
                    <span class="label"><?php _i18n( 'weather-airdens' ); ?></span>
                    <div>
                        <b><x><?php echo __number( airdens( $weather ), 2 ); ?></x>&#8239;kg/m<sup>3</sup></b>
                    </div>
                </li>
                <li>
                    <span class="label"><?php _i18n( 'weather-windchill' ); ?></span>
                    <div>
                        <b><?php echo temp_in( $windchill, 'c' ); ?></b>
                        <span>(<?php echo temp_in( $windchill * 1.8 + 32, 'f' ); ?>)</span>
                    </div>
                </li>
            </ul>
        </div>
        <div class="weather-skycond">
            <div class="vis-info">
                <?php echo vis_info( $weather, true ); ?>
            </div>
            <div class="chart"></div>
        </div>
        <div class="weather-raw">
            <span class="rawtxt"><?php echo $weather['raw']; ?></span>
        </div>
        <div class="weather-runways"></div>
    <?php } ?>
</div>