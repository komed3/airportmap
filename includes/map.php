<?php

    function _map(
        array $options = [],
        string $classes = ''
    ) {

        add_resource( 'map', 'css', 'map.css' );
        add_resource( 'map', 'js', 'map.js' );

        echo '<div class="map ' . $classes . '" map-data="' .
            base64_encode( json_encode(
                $options,
                JSON_NUMERIC_CHECK
            ) ) .
        '"></div>';

    }

?>