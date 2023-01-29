<?php

    $__site_canonical = 'traffic';

    $__site_title = i18n( 'traffic-title' );
    $__site_desc = i18n( 'traffic-desc' );

    $link = '<a href="https://opensky-network.org" target="_blank">The OpenSky Network</a>';

    add_resource( 'text', 'css', 'text.css' );
    add_resource( 'traffic', 'css', 'traffic.css' );

    _header();

?>
<div class="content-full traffic">
    <?php _map( [
        'type' => 'traffic',
        'navaids' => false,
        'waypoints' => false,
        'supress_sigmets' => true,
        'supress_day_night' => false,
        'position' => [
            'lat' => 40.7,
            'lon' => -74,
            'zoom' => 6
        ]
    ], 'full-screen minimal-ui' ); ?>
    <h1 class="primary-headline">
        <i class="icon">cell_tower</i>
        <span><?php echo $__site_title; ?></span>
    </h1>
    <div class="content-normal text-content">
        <p class="first"><?php _i18n( 'traffic-intro' ); ?></p>
        <p><?php _i18n( 'traffic-about', $link ); ?></p>
        <div class="traffic-credits">
            <h2><?php _i18n( 'traffic-credits-headline', $link ); ?></h2>
            <p><?php _i18n( 'traffic-credits' ); ?></p>
        </div>
    </div>
</div>
<?php _footer(); ?>