<?php

    $sigmets = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'sigmet
        WHERE   valid_from <= NOW()
        AND     valid_to >= NOW()
    ' )->fetch_all( MYSQLI_ASSOC );

    $__site_canonical = 'weather/sigmets';

    $__site_title = i18n( 'sigmets-title', count( $sigmets ) );
    $__site_desc = 'sigmets-desc';

    add_resource( 'sigmets', 'css', 'sigmet.css' );

    _header();

?>
<div class="content-normal">
    <h1><?php echo $__site_title; ?></h1>
    <div class="sigmets">
        <?php foreach( $sigmets as $sigmet ) { ?>
            <div class="sigmet sigmet-<?php echo $sigmet['hazard']; ?>">
                <div class="sigmet-header">
                    <h2 class="sigmet-title">
                        <?php echo sigmet_hazard( $sigmet ); ?>
                    </h2>
                    <h3 class="sigmet-subtitle">
                        <?php echo $sigmet['name']; ?>
                    </h3>
                </div>
                <div class="sigmet-raw rawtxt">
                    <?php echo $sigmet['raw']; ?>
                </div>
                <ul class="sigmet-list">
                    <li>
                        <i class="icon">schedule</i>
                        <span><?php echo sigmet_valid( $sigmet ); ?></span>
                    </li>
                    <li>
                        <i class="icon">near_me</i>
                        <span><?php echo sigmet_move( $sigmet ); ?></span>
                    </li>
                    <li>
                        <i class="icon">warning</i>
                        <span><?php echo sigmet_cng( $sigmet ); ?></span>
                    </li>
                    <?php if( !empty( $fl = sigmet_fl( $sigmet ) ) ) { ?><li>
                        <i class="icon">flight_takeoff</i>
                        <span><?php echo $fl; ?></span>
                    </li><?php } ?>
                </ul>
            </div>
        <?php } ?>
    </div>
</div>
<?php _footer(); ?>