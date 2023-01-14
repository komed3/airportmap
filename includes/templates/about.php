<?php

    add_resource( 'about', 'css', 'about.css' );

    $__site_canonical = $base . 'about';

    $__site_title = i18n( 'about-title' );
    $__site_desc = i18n( 'about-desc' );

    _header();

?>
<div class="content-full about">
    <div class="site-image">
        <div class="credits"><?php _i18n(
            'pix-credits',
            '<a href="https://pixabay.com/users/jfk_photography-25701175">Johannes Kirchherr</a>',
            '<a href="https://pixabay.com">Pixabay</a>'
        ); ?></div>
    </div>
    <h1 class="primary-headline"><?php _i18n( 'about-title' ); ?></h1>
    <div class="content-normal">
        <p class="first"><?php _i18n( 'site-welcome-text', __number( AIRPORT_ALL ) ); ?></p>
        <p><?php _i18n( 'about-code' ); ?></p>
        <h2 class="secondary-headline"><?php _i18n( 'about-goal' ); ?></h2>
        <p><?php _i18n( 'about-goal-primary' ); ?></p>
        <p><?php _i18n( 'about-goal-secondary' ); ?></p>
        <p class="warn"><?php _i18n( 'site-warning' ); ?></p>
        <h2 class="secondary-headline"><?php _i18n( 'about-source' ); ?></h2>
        <p><?php _i18n( 'about-source-sources' ); ?></p>
        <p><?php _i18n( 'about-source-airport' ); ?></p>
        <p><?php _i18n( 'about-source-image' ); ?></p>
        <p><?php _i18n( 'about-source-weather' ); ?></p>
        <p><?php _i18n( 'about-source-sigmet' ); ?></p>
        <p><?php _i18n( 'about-source-map' ); ?></p>
    </div>
</div>
<?php _footer(); ?>