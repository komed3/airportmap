<?php

    global $DB, $airport;

    if( empty( $airport ) ) {

        __404();

    }

?>
<div class="airport-nearby content-normal">
    <?php _map( [
        'type' => 'airport',
        'navaids' => true,
        'supress_sigmets' => false,
        'supress_day_night' => false,
        'position' => [
            'lat' => $airport['lat'],
            'lon' => $airport['lon'],
            'zoom' => 9
        ]
    ], 'minimal-ui' ); ?>
</div>