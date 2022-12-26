<?php

    function _map(
        array $options = [],
        string $classes = ''
    ) {

        add_resource( 'map', 'css', 'map.css' );

        add_resource( 'leaflet-terminator', 'js', 'L.Terminator.js' );
        add_resource( 'map', 'js', 'map.js' );

        ?>
            <div class="map <?php echo $classes; ?>" map-data="<?php echo base64_encode(
                json_encode(
                    $options,
                    JSON_NUMERIC_CHECK
                )
            ); ?>">
                <div class="map-overlay">
                    <div class="map-control">
                        <div class="map-control-group">
                            <button class="map-type-airport" map-action="type" data-type="airport" title="<?php _i18n( 'map-overlay-type-airport' ); ?>">
                                <i class="icon">location_searching</i>
                            </button>
                            <button class="map-type-weather" map-action="type" data-type="weather" title="<?php _i18n( 'map-overlay-type-weather' ); ?>">
                                <i class="icon">rainy</i>
                            </button>
                        </div>
                        <div class="map-control-group">
                            <button class="map-sigmet" map-action="sigmet" title="<?php _i18n( 'map-overlay-sigmet' ); ?>">
                                <i class="icon">cyclone</i>
                            </button>
                            <button class="map-day-night" map-action="day-night" title="<?php _i18n( 'map-overlay-day-night' ); ?>">
                                <i class="icon">nights_stay</i>
                            </button>
                            <button class="map-mypos" map-action="mypos" title="<?php _i18n( 'map-overlay-mypos' ); ?>">
                                <i class="icon">near_me</i>
                            </button>
                        </div>
                        <div class="map-control-group">
                            <button class="map-zoom-in" map-action="zoom-in" title="<?php _i18n( 'map-overlay-zoom-in' ); ?>">
                                <i class="icon">add</i>
                            </button>
                            <button class="map-zoom-out" map-action="zoom-out" title="<?php _i18n( 'map-overlay-zoom-out' ); ?>">
                                <i class="icon">remove</i>
                            </button>
                        </div>
                    </div>
                    <div class="map-infobox"></div>
                    <div class="map-legend"></div>
                    <button class="map-scroll-below" map-action="scroll-below" title="<?php _i18n( 'map-overlay-scroll' ); ?>">
                        <i class="icon">arrow_downward</i>
                    </button>
                </div>
            </div>
        <?php

    }

?>