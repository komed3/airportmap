<?php

    $sigmets = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'sigmet
        WHERE   valid_from <= NOW()
        AND     valid_to >= NOW()
    ' )->fetch_all( MYSQLI_ASSOC );

    $__site_canonical = 'weather/sigmets';

    $__site_title = i18n( 'sigmets-title', __number( count( $sigmets ) ) );
    $__site_desc = i18n( 'sigmets-desc' );

    add_resource( 'sigmets', 'css', 'sigmet.css' );
    add_resource( 'sigmets', 'js', 'sigmet.min.js' );

    _header();

?>
<div class="content-normal">
    <h1><?php echo $__site_title; ?></h1>
    <div class="filter sigmets-filter">
        <div class="filter-group">
            <label for="sigmet-filter_hazard">
                <?php _i18n( 'sigmet-filter-hazard' ); ?>
            </label>
            <select id="sigmet-filter_hazard" filter="hazard">
                <option value=""><?php _i18n( 'filter-all' ); ?></option>
                <?php foreach( array_filter( array_unique( array_column( $sigmets, 'hazard' ) ) ) as $hazard ) { ?>
                    <option value="<?php echo $hazard; ?>">
                        <?php _i18n( 'hazard-' . $hazard ); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="sigmet-filter_change">
                <?php _i18n( 'sigmet-filter-change' ); ?>
            </label>
            <select id="sigmet-filter_change" filter="change">
                <option value=""><?php _i18n( 'filter-all' ); ?></option>
                <?php foreach( array_filter( array_unique( array_column( $sigmets, 'cng' ) ) ) as $change ) { ?>
                    <option value="<?php echo $change; ?>">
                        <?php _i18n( 'change-' . $change ); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="sigmets">
        <?php foreach( $sigmets as $sigmet ) { ?>
            <div class="sigmet hazard-<?php echo $sigmet['hazard']; ?> change-<?php echo $sigmet['cng'] ?? 'NC'; ?>">
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