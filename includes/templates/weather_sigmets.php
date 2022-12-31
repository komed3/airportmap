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
    add_resource( 'sigmets', 'js', 'sigmet.js' );

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
                    <?php echo sigmet_info( $sigmet ); ?>
                </ul>
            </div>
        <?php } ?>
    </div>
</div>
<?php _footer(); ?>