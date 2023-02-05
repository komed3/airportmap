<?php

    global $airport;

    if( empty( $airport ) ) {

        __404();

    }

    $codes = array_filter( array_intersect_key( $airport, [
        'ICAO' => true, 'IATA' => true, 'GPS' => true
    ] ) );

?>
<div class="airport-info content-normal">
    <div class="info">
        <ul class="infolist">
            <li>
                <span class="label"><?php _i18n( 'info-name' ); ?></span>
                <div><span><?php echo $airport['name']; ?></span></div>
            </li>
            <li>
                <span class="label"><?php echo implode( '&nbsp;/ ', array_keys( $codes ) ); ?></span>
                <div><span><?php echo implode( '&nbsp;/ ', $codes ); ?></span></div>
            </li>
            <li>
                <span class="label"><?php _i18n( 'info-type' ); ?></span>
                <div><?php echo airport_type_link( $airport['type'] ); ?></div>
            </li>
            <?php if( $airport['restriction'] ) { ?><li>
                <span class="label"><?php _i18n( 'info-res' ); ?></span>
                <div><?php echo airport_res_link( $airport['restriction'] ); ?></div>
            </li><?php } ?>
            <li>
                <span class="label"><?php _i18n( 'info-region' ); ?></span>
                <div>
                    <span><?php echo region_link( 'region', $airport['region'] ); ?></span>
                    <span>/</span>
                    <span><?php echo region_link( 'country', $airport['country'] ); ?></span>
                </div>
            </li>
            <?php if( $tz = airport_timezone( $airport ) ) { ?><li>
                <span class="label"><?php _i18n( 'info-timezone' ); ?></span>
                <div>
                    <span><?php echo date(
                        i18n( 'clock-time' ),
                        time() + ( $airport['gmt_offset'] * 60 )
                    ); ?></span>
                    <span>(<?php echo tz_link( $tz, true ); ?>)</span>
                </div>
            </li><?php } ?>
            <?php if( $airport['municipality'] ) { ?><li>
                <span class="label"><?php _i18n( 'info-municipality' ); ?></span>
                <div><span><?php echo $airport['municipality']; ?></span></div>
            </li><?php } ?>
            <li>
                <span class="label"><?php _i18n( 'info-coords' ); ?></span>
                <div><?php echo __DMS_coords( $airport['lat'], $airport['lon'] ); ?></div>
            </li>
            <li>
                <span class="label"><?php _i18n( 'info-alt' ); ?></span>
                <div>
                    <span><?php echo alt_in( (int) $airport['alt'] ); ?></span>
                    <span>(<?php echo alt_in( (int) $airport['alt'] / 3.281, 'm&nbsp;MSL' ); ?>)</span>
                </div>
            </li>
        </ul>
    </div>
    <?php _map( [
        'type' => 'airport',
        'navaids' => true,
        'supress_sigmets' => true,
        'supress_day_night' => true,
        'position' => [
            'lat' => $airport['lat'],
            'lon' => $airport['lon'],
            'zoom' => 12
        ]
    ], 'minimal-ui' ); ?>
</div>