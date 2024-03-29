<?php add_resource( 'cookie', 'css', 'cookie.min.css' ); ?>
<div class="cookie" cookie>
    <i class="icon cookie-icon">cookie</i>
    <div class="cookie-content">
        <h1><?php _i18n( 'cookie-title' ) ?></h1>
        <p><?php _i18n( 'cookie-text' ); ?></p>
        <div class="cookie-save">
            <a href="#" class="accept" data-action="cookie" data-cookie="1"><?php _i18n( 'cookie-accept' ); ?></a>
            <a href="#" data-action="cookie" data-cookie="0"><?php _i18n( 'cookie-reject' ); ?></a>
            <a href="<?php _base_url( 'privacy' ); ?>"><?php _i18n( 'site-privacy' ); ?></a>
        </div>
    </div>
</div>