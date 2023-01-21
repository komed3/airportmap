<?php

    define( 'NO_KEY', true );

    require_once __DIR__ . '/api.php';

    load_requirements( 'language', 'content', 'airport', 'weather' );

    i18n_load( $_GET['lang'] ?? LOCALE );

    $ICAO = strtoupper( trim( $_GET['airport'] ?? '' ) );
    $airport = airport_by( 'ICAO', $ICAO );

    if( $airport ) {

        $weather = airport_weather( $airport );

    } else $weather = null;

    $__site_canonical = base_url( 'airport/' . $ICAO . '/info' );

    $__site_title = $airport['name'] ?? i18n( 'unknown' );
    $__site_desc = i18n( 'embed-info', $__site_title, $ICAO );

    add_resource( 'base', 'css', 'base.css' );
    add_resource( 'embed', 'css', 'embed.css' );

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="author" content="Paul Köhler (komed3)" />
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1" />
        <?php _site_header(); ?>
    </head>
    <body class="cat-<?php echo $weather['flight_cat'] ?? 'UNK'; ?>">
        <div id="wrapper">
            <?php if( !empty( $airport ) ) { ?>
                <div class="embed airport">
                    <div class="embed-header">
                        <h1><?php echo $ICAO; ?></h1>
                        <h2><?php echo $__site_title; ?></h2>
                    </div>
                    <?php if( !empty( $weather ) ) { ?>
                        <div class="embed-weather">
                            <?php if( $weather['flight_cat'] ) { ?>
                                <div class="cat">
                                    <span><?php _i18n( 'cat-' . $weather['flight_cat'] ); ?></span>
                                </div>
                                <div class="vis"><?php echo vis_info( $weather ); ?></div>
                            <?php } else { ?>
                                <div class="cat">
                                    <span><?php _i18n( 'cat-unk' ); ?></span>
                                </div>
                                <div class="vis"><?php _i18n( 'sky-unknown' ); ?></div>
                            <?php } ?>
                            <div class="icon"><?php echo wx_icon( $weather ); ?></div>
                            <div class="info">
                                <div class="temp">
                                    <b><?php echo temp_in( (int) $weather['temp'], 'c' ); ?></b>
                                    <span>(<?php echo temp_in( ( (int) $weather['temp'] ) * 1.8 + 32, 'f' ); ?>)</span>
                                </div>
                                <div class="wx"><?php echo ucfirst( wx( $weather ) ); ?></div>
                                <div class="wind"><?php echo wind_info( $weather ); ?></div>
                            </div>
                        </div>
                        <hr />
                    <?php } ?>
                    <ul class="embed-list">
                        <li>
                            <i class="icon">luggage</i>
                            <span><?php _i18n( 'airport-type-' . $airport['type'] ); ?></span>
                        </li>
                        <li>
                            <i class="icon">location_on</i>
                            <span><?php echo region_name( 'country', $airport['country'] ); ?></span>
                        </li>
                        <li>
                            <i class="icon">near_me</i>
                            <?php echo __DMS_coords( $airport['lat'], $airport['lon'] ); ?>
                        </li>
                        <li>
                            <i class="icon">flight_takeoff</i>
                            <span><?php echo alt_in( (int) $airport['alt'] ); ?></span>
                            <span>(<?php echo alt_in( (int) $airport['alt'] / 3.281, 'm&#8239;MSL' ); ?>)</span>
                        </li>
                        <?php if( $airport['timezone'] ) { ?><li>
                            <i class="icon">schedule</i>
                            <span><?php echo date(
                                i18n( 'clock-time' ),
                                time() + ( $airport['gmt_offset'] * 60 )
                            ); ?></span>
                            <span>(<?php echo $airport['timezone']; ?>)</span>
                        </li><?php } ?>
                    </ul>
                    <div class="embed-space"></div>
                    <a class="embed-link" href="<?php echo $__site_canonical; ?>" target="_blank">
                        <span><?php _i18n( 'view-airport' ); ?></span>
                        <i class="icon">chevron_right</i>
                    </a>
                    <p class="embed-credits"><?php _i18n( 'embed-credits', date( 'Y' ), base_url() ); ?></p>
                </div>
            <?php } else { ?>
                <div class="embed empty">
                    <div class="embed-header">
                        <h1><?php echo $ICAO; ?></h1>
                        <h2><?php echo $__site_title; ?></h2>
                    </div>
                    <p class="embed-empty"><?php _i18n( 'embed-empty' ); ?></p>
                    <div class="embed-space"></div>
                    <a class="embed-link" href="<?php _base_url( 'airports' ); ?>" target="_blank">
                        <span><?php _i18n( 'embed-empty-link' ); ?></span>
                        <i class="icon">chevron_right</i>
                    </a>
                    <p class="embed-credits"><?php _i18n( 'embed-credits', date( 'Y' ), base_url() ); ?></p>
                </div>
            <?php } ?>
        </div>
        <?php _site_end(); ?>
    </body>
</html>