<?php

    global $DB, $path, $airport, $base;

    if( empty( $airport ) ) {

        __404();

    }

    $path[3] = $path[3] ?? 'nearest';

?>
<div class="airport-nearby content-normal">
    <?php

        _map( [
            'type' => 'airport',
            'navaids' => true,
            'supress_sigmets' => false,
            'supress_day_night' => false,
            'position' => [
                'lat' => $airport['lat'],
                'lon' => $airport['lon'],
                'zoom' => 9
            ]
        ], 'minimal-ui' );

        _site_nav( [ null, [
            'i18n' => 'nearby-nearest',
            'url' => $base . 'nearby/nearest',
            'check' => 'nearest'
        ], [
            'i18n' => 'nearby-largest',
            'url' => $base . 'nearby/largest',
            'check' => 'largest'
        ], [
            'i18n' => 'nearby-airports',
            'url' => $base . 'nearby/airports',
            'check' => 'airports'
        ], [
            'i18n' => 'nearby-service',
            'url' => $base . 'nearby/service',
            'check' => 'service'
        ], [
            'i18n' => 'nearby-all',
            'url' => $base . 'nearby/all',
            'check' => 'all'
        ], null ], 'site-tabs content-normal', 3 );

    ?>
</div>