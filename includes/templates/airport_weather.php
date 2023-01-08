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
    <?php } ?>
</div>