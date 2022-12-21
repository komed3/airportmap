<?php

    define( 'NO_TOKEN', true );

    require_once __DIR__ . '/../apm.php';

    api_auth();

    $metars = [];

    foreach( array_slice( explode( PHP_EOL, file_get_contents(
        'https://www.aviationweather.gov/adds/dataserver_current/current/metars.cache.csv'
    ) ), 6 ) as $metar ) {



    }

?>