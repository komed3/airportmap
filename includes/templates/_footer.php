            </div>
            <div id="footer">
                <div class="column">
                    <a href="<?php _base_url(); ?>" title="<?php _i18n( 'site-title-default' ); ?>" class="site-logo">
                        <img src="<?php _base_url( 'favicon-48x48.png' ); ?>" alt="" />
                    </a>
                    <h3>Airportmap</h3>
                    <p class="about"><?php _i18n( 'footer-about', __number( floor( AIRPORT_ALL / 500 ) * 500 ) ); ?></p>
                    <p class="credits"><?php _i18n( 'footer-credits', date( 'Y' ) ); ?></p>
                </div>
                <div class="column">
                    <h3><?php _i18n( 'footer-headline-discover' ); ?></h3>
                    <?php _site_nav( [ [
                        'i18n' => 'footer-nav-airports',
                        'url' => 'airports'
                    ], [
                        'i18n' => 'footer-nav-list',
                        'url' => 'list'
                    ], [
                        'i18n' => 'footer-nav-weather',
                        'url' => 'weather'
                    ], [
                        'i18n' => 'footer-nav-sigmets',
                        'url' => 'weather/sigmets'
                    ], [
                        'i18n' => 'footer-nav-traffic',
                        'url' => 'traffic'
                    ], [
                        'i18n' => 'footer-nav-vicinity',
                        'url' => 'vicinity'
                    ] ], 'footer-nav', -1 ); ?>
                </div>
                <div class="column">
                    <h3><?php _i18n( 'footer-headline-developer' ); ?></h3>
                    <?php _site_nav( [ [
                        'i18n' => 'footer-nav-about',
                        'url' => 'about'
                    ], [
                        'i18n' => 'footer-nav-data',
                        'url' => 'data'
                    ], [
                        'i18n' => 'footer-nav-embed',
                        'url' => 'embed'
                    ], [
                        'i18n' => 'footer-nav-stats',
                        'url' => 'stats'
                    ], [
                        'i18n' => 'footer-nav-github',
                        'external' => 'https://github.com/komed3/airportmap'
                    ] ], 'footer-nav', -1 ); ?>
                </div>
                <div class="column">
                    <h3><?php _i18n( 'footer-headline-support' ); ?></h3>
                    <?php _site_nav( [ [
                        'i18n' => 'site-privacy',
                        'url' => 'privacy'
                    ], [
                        'i18n' => 'footer-nav-issues',
                        'external' => 'https://github.com/komed3/airportmap/issues'
                    ], [
                        'i18n' => 'footer-nav-donate',
                        'external' => 'https://github.com/komed3/airportmap'
                    ] ], 'footer-nav', -1 ); ?>
                    <h3><?php _i18n( 'footer-headline-settings' ); ?></h3>
                    <div class="language-selector">
                        <select data-action="select-language">
                            <?php foreach( LANGUAGES as $lng ) { ?>
                                <option value="<?php echo $lng; ?>" <?php if( $lng == i18n_locale() ) { ?>selected<?php } ?>>
                                    <?php _i18n( 'site-language-' . $lng ); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <i class="icon">language</i>
                    </div>
                    <div class="theme-selector theme-<?php echo $_COOKIE['theme'] ?? 'light'; ?>">
                        <a href="#" data-action="theme" theme="light">
                            <i class="icon">light_mode</i>
                            <span class="label"><?php _i18n( 'theme-light' ); ?></span>
                        </a>
                        <a href="#" data-action="theme" theme="dark">
                            <i class="icon">dark_mode</i>
                            <span class="label"><?php _i18n( 'theme-dark' ); ?></span>
                        </a>
                    </div>
                </div>
                <div class="share">
                    <i class="icon">share</i>
                    <span class="label"><?php _i18n( 'share-it' ); ?></span>
                    <?php _share_links(); ?>
                </div>
            </div>
            <div id="warning">
                <span><?php _i18n( 'site-warning' ); ?></span>
            </div>
            <div id="scroll-to-top" data-action="scroll-to-top" title="<?php _i18n( 'scroll-to-top' ); ?>">
                <i class="icon">arrow_upward</i>
            </div>
            <?php if( !isset( $_COOKIE['cookie_test'] ) ) {
                load_tpl_part( '_cookie' );
            } ?>
        </div>
        <?php _site_end(); ?>
    </body>
</html>