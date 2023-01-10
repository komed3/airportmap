<?php

    global $DB, $path, $base, $airport;

    if( empty( $airport ) ) {

        __404();

    }

    $stations = stations_at(
        $airport['lat'],
        $airport['lon']
    );

?>
<a name="__weather" class="anchor" tabindex="-1"></a>
<div class="airport-weather content-normal">
    <?php if( empty( $stations ) ) { ?>
        <p><?php _i18n( 'airport-weather-empty' ); ?></p>
    <?php } else {

        $index = (int) array_search(
            strtoupper( $path[3] ?? 0 ),
            array_column( $stations, 'ICAO' )
        );

        $weather = $stations[ $index ];

    ?>
        <div class="weather-station <?php echo $weather['distance'] < 1 ? 'on-site' : ''; ?>">
            <div class="label"><?php _i18n( 'airport-weather-station' ); ?></div>
            <select data-action="select-station" data-base="<?php echo $base; ?>weather/" data-jump="__weather">
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
            </div>
        </div>
        <div class="weather-list">
            <ul>
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
                        <b><?php echo __number( relhum( $weather ), 1 ); ?>&#8239;%</b>
                    </div>
                </li>
                <li>
                    <span class="label"><?php _i18n( 'weather-airdens' ); ?></span>
                    <div>
                        <b><?php echo __number( airdens( $weather ), 2 ); ?>&#8239;kg/m<sup>3</sup></b>
                    </div>
                </li>
                <li>
                    <span class="label"><?php _i18n( 'weather-windchill' ); ?></span>
                    <div>
                        <b><?php echo temp_in( $windchill = windchill( $weather ), 'c' ); ?></b>
                        <span>(<?php echo temp_in( $windchill * 1.8 + 32, 'f' ); ?>)</span>
                    </div>
                </li>
            </ul>
        </div>
        <div class="weather-skycond">
            <div class="vis-info">
                <?php echo vis_info( $weather, true ); ?>
            </div>
            <?php echo sky_chart( $weather ); ?>
        </div>
        <div class="weather-raw">
            <span class="rawtxt"><?php echo $weather['raw']; ?></span>
        </div>
        <div class="weather-wind">
            <?php echo wind_rwy( $weather, $airport ); ?>
        </div>
    <?php } ?>
</div>