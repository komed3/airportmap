<?php

    add_resource( '404', 'css', '404.css' );

    _header();

?>
<div class="content-full error-404">
    <div class="credits"><?php _i18n(
        '404-credits',
        '<a href="https://pixabay.com/users/tobiasrehbein-11751606">Tobias Rehbein</a>',
        '<a href="https://pixabay.com">Pixabay</a>'
    ); ?></div>
    <h1><span><?php echo implode( '</span><span>', str_split( i18n( '404-teaser' ) ) ); ?></span></h1>
    <h2><?php _i18n( '404-headline' ); ?></h2>
    <?php load_tpl_part( '_searchform' ); ?>
</div>
<?php _footer(); ?>