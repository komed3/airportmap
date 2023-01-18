<?php

    add_resource( 'text', 'css', 'text.css' );

    $__site_canonical = $base . 'embed';

    $__site_title = i18n( 'embed-title' );
    $__site_desc = i18n( 'embed-desc' );

    _header();

?>
<div class="content-normal embed text-content">
    <h1><?php _i18n( 'embed-title' ); ?></h1>
    <p class="first"><?php _i18n( 'embed-intro' ); ?></p>
    <p><?php _i18n( 'embed-usage', base_url( 'privacy' ) ); ?></p>
</div>
<?php _footer(); ?>