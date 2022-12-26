<?php

    function _map(
        array $options = [],
        string $classes = ''
    ) {

        add_resource( 'map', 'css', 'map.css' );
        add_resource( 'map', 'js', 'map.js' );

        ?>
            <div class="map <?php echo $classes; ?>" map-data="<?php echo base64_encode(
                json_encode(
                    $options,
                    JSON_NUMERIC_CHECK
                )
            ); ?>">
                <div class="map-overlay">
                    <div class="map-control"></div>
                    <div class="map-actions"></div>
                    <div class="map-legend"></div>
                    <button class="map-scroll-below" data-action="scroll-below-map" title="<?php _i18n( 'map-overlay-scroll' ); ?>">
                        <i class="icon">arrow_downward</i>
                    </button>
                </div>
            </div>
        <?php

    }

?>