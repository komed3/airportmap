<?php

    add_resource( 'welcome', 'css', 'welcome.css' );

    _header();

    _map( [
        'save_position' => true,
        'save_type' => true
    ], 'full-screen full-overlay windbug' );

?>
<div class="welcome content-wide">
    <h1><?php _i18n( 'site-welcome-headline' ); ?></h1>
    <p><?php _i18n( 'site-welcome-text', __number( AIRPORT_ALL ) ); ?></p>
    <?php load_tpl_part( '_searchform' ); ?>
</div>
<div class="welcome-stats content-full">
    <div class="stats-col">
        <div class="value"><?php echo __number( AIRPORT_STATS['large'] ); ?></div>
        <div class="label"><?php _i18n( 'airports' ); ?></div>
    </div>
</div>
<?php _footer(); ?>