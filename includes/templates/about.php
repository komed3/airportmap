<?php

    add_resource( 'text', 'css', 'text.min.css' );
    add_resource( 'about', 'css', 'about.min.css' );

    $__site_canonical = 'about';

    $__site_title = i18n( 'about-title' );
    $__site_desc = i18n( 'about-desc' );

    _header();

?>
<div class="content-full about text-content">
    <div class="site-image">
        <div class="credits"><?php _i18n(
            'pix-credits',
            '<a href="https://pixabay.com/users/mmamontov-15693250">Mikhail Mamontov</a>',
            '<a href="https://pixabay.com">Pixabay</a>'
        ); ?></div>
    </div>
    <h1 class="primary-headline"><?php _i18n( 'about-title' ); ?></h1>
    <?php _site_nav( [ [
        'i18n' => 'footer-nav-stats',
        'url' => $base . 'stats'
    ], [
        'i18n' => 'footer-nav-data',
        'url' => $base . 'data'
    ], [
        'i18n' => 'footer-nav-embed',
        'url' => $base . 'embed'
    ], [
        'i18n' => 'site-privacy',
        'url' => $base . 'privacy'
    ] ], 'site-tabs content-normal' ); ?>
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
        <p><?php _i18n( 'about-source-traffic' ); ?></p>
        <hr />
        <h2 class="secondary-headline"><?php _i18n( 'map-legend' ); ?></h2>
        <div class="map-legend">
            <div class="legend-box">
                <p><?php _i18n( 'map-legend-type' ); ?></p>
                <ul class="legend">
                    <?php foreach( [ 'large', 'medium', 'small', 'heliport', 'altiport', 'seaplane', 'closed' ] as $type ) { ?>
                        <li class="airport-<?php echo $type; ?>">
                            <mapicon></mapicon>
                            <span><?php _i18n( 'airport-type-' . $type ); ?></span>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="legend-box">
                <p><?php _i18n( 'map-legend-weather' ); ?></p>
                <ul class="legend">
                    <?php foreach( [ 'VFR', 'MVFR', 'IFR', 'LIFR', 'UNK' ] as $cat ) { ?>
                        <li class="cat-<?php echo $cat; ?>">
                            <wxicon></wxicon>
                            <span><?php _i18n( 'cat-' . $cat . '-label' ); ?> (<?php _i18n( 'cat-' . $cat ); ?>)</span>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="legend-box">
                <p><?php _i18n( 'map-legend-restriction' ); ?></p>
                <ul class="legend">
                    <?php foreach( [ 'public', 'joint_use', 'military', 'private' ] as $res ) { ?>
                        <li class="airport-medium restriction-<?php echo $res; ?>">
                            <mapicon></mapicon>
                            <span><?php _i18n( 'airport-res-' . $res ); ?></span>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="legend-box">
                <p><?php _i18n( 'map-legend-navaid' ); ?></p>
                <ul class="legend">
                    <?php foreach( [ 'DME', 'NDB', 'NDB-DME', 'VOR', 'VOR-DME', 'TACAN', 'VORTAC' ] as $navaid ) { ?>
                        <li class="navaid-<?php echo $navaid; ?>">
                            <navicon></navicon>
                            <span><?php _i18n( 'navaid-' . $navaid ); ?></span>
                        </li>
                    <?php } ?>
                    <li class="waypoint">
                        <wpicon></wpicon>
                        <span><?php _i18n( 'waypoints' ); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php _footer(); ?>