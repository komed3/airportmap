<?php

    require_once __DIR__ . '/api.php';

    api_exit( [
        'sigmets' => $DB->query( '
            SELECT  _id, hazard, polygon
            FROM    ' . DB_PREFIX . 'sigmet
            WHERE   valid_from <= NOW()
            AND     valid_to >= NOW()
        ' )->fetch_all( MYSQLI_ASSOC )
    ] );

?>