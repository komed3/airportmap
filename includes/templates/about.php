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
</div>
<?php _footer(); ?>