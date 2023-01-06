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
                        'i18n' => 'footer-nav-map',
                        'url' => ''
                    ], [
                        'i18n' => 'footer-nav-search-airports',
                        'url' => 'airports'
                    ], [
                        'i18n' => 'footer-nav-weather-forecast',
                        'url' => 'weather'
                    ], [
                        'i18n' => 'footer-nav-sigmets',
                        'url' => 'weather/sigmets'
                    ], [
                        'i18n' => 'footer-nav-stats',
                        'url' => 'stats'
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
                    <h3><?php _i18n( 'footer-headline-language' ); ?></h3>
                    <div class="language-selector">
                        <select data-action="select-language">
                            <option value="en-US"><?php _i18n( 'site-language-en-us' ); ?></option>
                            <option value="de-DE"><?php _i18n( 'site-language-de-de' ); ?></option>
                        </select>
                        <i class="icon">language</i>
                    </div>
                </div>
            </div>
            <div id="warning">
                <span><?php _i18n( 'site-warning' ); ?></span>
            </div>
            <div id="scroll-to-top" data-action="scroll-to-top" title="<?php _i18n( 'scroll-to-top' ); ?>">
                <i class="icon">arrow_upward</i>
            </div>
        </div>
        <?php _site_end(); ?>
    </body>
</html>