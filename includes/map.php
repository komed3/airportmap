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
                            <button class="map-type-airport" map-action="type" map-type="airport" title="<?php _i18n( 'map-overlay-type-airport' ); ?>">
                                <i class="icon">location_searching</i>
                            </button>
                            <button class="map-type-weather" map-action="type" map-type="weather" title="<?php _i18n( 'map-overlay-type-weather' ); ?>">
                                <i class="icon">rainy</i>
                            </button>
                        </div>
                        <div class="map-control-group">
                            <button class="map-sigmet" map-action="sigmet" title="<?php _i18n( 'map-overlay-sigmet' ); ?>">
                                <i class="icon">cyclone</i>
                            </button>
                            <button class="map-navaids" map-action="navaids" title="<?php _i18n( 'map-overlay-navaids' ); ?>">
                                <i class="icon">cell_tower</i>
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
                    <div class="map-infobox">
                        <div class="infobox-header">
                            <div class="infobox-close" map-action="close-infobox" title="<?php _i18n( 'map-overlay-close' ); ?>">
                                <i class="icon">close</i>
                            </div>
                            <h3 class="infobox-title"></h3>
                            <h4 class="infobox-subtitle"></h4>
                        </div>
                        <div class="infobox-content"></div>
                        <a href="" class="infobox-link">
                            <span class="infobox-linktext"></span>
                            <i class="icon">chevron_right</i>
                        </a>
                    </div>
                    <button class="map-scroll-below" map-action="scroll-below" title="<?php _i18n( 'map-overlay-scroll' ); ?>">
                        <i class="icon">arrow_downward</i>
                    </button>
                </div>
            </div>
        <?php

    }

?>