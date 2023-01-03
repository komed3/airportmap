<?php

    global $airport;

    if( empty( $airport ) ) {

        __404();

    }

    $codes = array_intersect_key( $airport, [
        'ICAO' => true, 'IATA' => true, 'GPS' => true
    ] );

?>
<div class="airport-info content-normal">
    <div class="info">
        <ul class="infolist">
            <li>
                <span class="label"><?php _i18n( 'info-name' ); ?></span>
                <div><?php echo $airport['name']; ?></div>
            </li>
            <li>
                <span class="label"><?php echo implode( ' / ', array_keys( $codes ) ); ?></span>
                <div><?php echo implode( ' / ', $codes ); ?></div>
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
            <li>
                <span class="label"><?php _i18n( 'info-coords' ); ?></span>
                <div><?php echo __DMS_coords( $airport['lat'], $airport['lon'] ); ?></div>
            </li>
            <li>
                <span class="label"><?php _i18n( 'info-alt' ); ?></span>
                <div>
                    <span><?php echo alt_in( (int) $airport['alt'] ); ?></span>
                    <span><?php echo alt_in( (int) $airport['alt'] / 3.281, 'm&#8239;MSL' ); ?></span>
                </div>
            </li>
        </ul>
    </div>
    <?php ?>
</div>