<?php

    require_once __DIR__ . '/../apm.php';

    $content = '<div class="map" data-map="' . base64_encode( '{"zoom":8}' ) . '"></div>';

    echo json_encode( [
        'title' => 'Discover airports all over the world',
        'content' => $content,
        'page' => 'map'
    ] );

?>