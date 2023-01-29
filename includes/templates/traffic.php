<?php

    $__site_canonical = 'traffic';

    $__site_title = i18n( 'traffic-title' );
    $__site_desc = i18n( 'traffic-desc' );

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
</div>
<?php _footer(); ?>