<?php add_resource( 'cookie', 'css', 'cookie.css' ); ?>
<div class="cookie">
    <i class="icon cookie-icon">cookie</i>
    <div class="cookie-content">
        <h1><?php _i18n( 'cookie-title' ) ?></h1>
        <p><?php _i18n( 'cookie-text' ); ?></p>
        <div class="cookie-save">
            <span><?php _i18n( 'cookie-label' ); ?></span>
            <a href="#" data-action="cookie-accept"><?php _i18n( 'cookie-accept' ); ?></a>
            <a href="#" data-action="cookie-reject"><?php _i18n( 'cookie-reject' ); ?></a>
            <a href="<?php _base_url( 'privacy' ); ?>"><?php _i18n( 'site-privacy' ); ?></a>
        </div>
    </div>
</div>