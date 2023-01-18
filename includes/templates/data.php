<?php

    add_resource( 'text', 'css', 'text.css' );

    $__site_canonical = $base . 'data';

    $__site_title = i18n( 'data-title' );
    $__site_desc = i18n( 'data-desc' );

    _header();

?>
<div class="content-normal data text-content">
    <h1><?php _i18n( 'data-title' ); ?></h1>
    <p class="first"><?php _i18n( 'data-open-source' ); ?></p>
    <p><?php _i18n( 'data-usage' ); ?></p>
    <h2><?php _i18n( 'data-export-headline' ); ?></h2>
    <p><?php _i18n( 'data-export' ); ?></p>
    <dl>
        <?php foreach( [ 'airport', 'frequency', 'runway', 'navaid' ] as $file ) { ?>
            <dt>
                <b><?php _i18n( 'data-file-' . $file ); ?></b>
                <a href="https://github.com/komed3/airportmap-database/raw/master/<?php echo $file; ?>.sql" target="_blank" download>SQL</a>
                <a href="https://github.com/komed3/airportmap-database/raw/master/<?php echo $file; ?>.csv" target="_blank" download>CSV</a>
            </dt>
            <dd><?php _i18n( 'data-desc-' . $file ); ?></dd>
        <?php } ?>
    </dl>
    <p><?php _i18n( 'data-repo' ); ?></p>
</div>
<?php _footer(); ?>