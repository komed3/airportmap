<?php

    add_resource( 'stats', 'css', 'stats.css' );

    $__site_canonical = $base . 'stats';

    $__site_title = i18n( 'airport-stats-title' );
    $__site_desc = i18n( 'airport-stats-desc' );

    _header();

?>
<div class="content-full stats">
    <div class="stats-intro">
        <div class="credits"><?php _i18n(
            'pix-credits',
            '<a href="https://pixabay.com/users/arminep-8300920">Armin Forster</a>',
            '<a href="https://pixabay.com">Pixabay</a>'
        ); ?></div>
        <h1><?php _i18n( 'airport-stats-title' ); ?></h1>
        <h2><?php _i18n( 'airport-stats-intro' ); ?></h2>
    </div>
    <div class="stats-section"></div>
    <div class="stats-section dark"></div>
    <div class="stats-section"></div>
    <div class="stats-section dark"></div>
</div>
<?php _footer(); ?>