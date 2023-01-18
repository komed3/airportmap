<?php

    add_resource( 'text', 'css', 'text.css' );

    $__site_canonical = $base . 'privacy';

    $__site_title = i18n( 'site-privacy' );
    $__site_desc = i18n( 'site-privacy-desc' );

    _header();

?>
<div class="content-normal privacy text-content">
    <h1><?php _i18n( 'site-privacy' ); ?></h1>
    <p class="first"><?php _i18n( 'privacy-consens' ); ?></p>
    <h2><?php _i18n( 'privacy-storage-headline' ); ?></h2>
    <p><?php _i18n( 'privacy-storage' ); ?></p>
    <h2><?php _i18n( 'privacy-services-headline' ); ?></h2>
    <p><?php _i18n( 'privacy-services' ); ?></p>
    <p><?php _i18n( 'privacy-geolocation' ); ?></p>
    <h2><?php _i18n( 'privacy-cookies-headline' ); ?></h2>
    <p><?php _i18n( 'privacy-cookies' ); ?></p>
    <p><?php _i18n( 'privacy-contact' ); ?></p>
</div>
<?php _footer(); ?>