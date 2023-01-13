<?php

    function _stats_grid(
        array $raw = []
    ) {

        $boxes = [];

        foreach( $raw as $row ) {

            $boxes[] = '<div class="stats-box">
                <i class="icon">' . $row['icon'] . '</i>
                <div class="info">
                    <div class="value">
                        <span>' . implode( '</span><span>', $row['value'] ) . '</span>
                    </div>
                    <div class="label">' . $row['label'] . '</div>
                </div>
            </div>';

        }

        echo '<div class="stats-grid">
            ' . implode( '', $boxes ) . '
        </div>';

    }

    add_resource( 'stats', 'css', 'stats.css' );

    $__site_canonical = $base . 'stats';

    $__site_title = i18n( 'stats-title' );
    $__site_desc = i18n( 'stats-desc' );

    _header();

?>
<div class="content-full stats">
    <div class="site-image">
        <div class="credits"><?php _i18n(
            'pix-credits',
            '<a href="https://pixabay.com/users/arminep-8300920">Armin Forster</a>',
            '<a href="https://pixabay.com">Pixabay</a>'
        ); ?></div>
    </div>
    <h1 class="primary-headline">
        <b><?php _i18n( 'stats-title' ); ?></b>
    </h1>
    <div class="stats-section">
        <h2 class="secondary-headline"><?php _i18n( 'stats-count' ); ?></h2>
        <p><?php _i18n( 'stats-count-desc' ); ?></p>
        <?php _stats_grid( [ [
            'icon' => 'luggage',
            'label' => i18n( 'stats-airports' ),
            'value' => [
                __number( AIRPORT_ALL )
            ]
        ], [
            'icon' => 'flight_takeoff',
            'label' => i18n( 'stats-active-runways' ),
            'value' => [
                __number( $DB->query( '
                    SELECT  COUNT( _id ) AS cnt
                    FROM    ' . DB_PREFIX . 'runway
                    WHERE   inuse = 1
                ' )->fetch_object()->cnt )
            ]
        ], [
            'icon' => 'cell_tower',
            'label' => i18n( 'stats-navaids' ),
            'value' => [
                __number( $DB->query( '
                    SELECT  COUNT( _id ) AS cnt
                    FROM    ' . DB_PREFIX . 'navaid
                ' )->fetch_object()->cnt )
            ]
        ], [
            'icon' => 'headset_mic',
            'label' => i18n( 'stats-frequencies' ),
            'value' => [
                __number( $DB->query( '
                    SELECT  COUNT( _id ) AS cnt
                    FROM    ' . DB_PREFIX . 'frequency
                ' )->fetch_object()->cnt )
            ]
        ], [
            'icon' => 'photo_camera',
            'label' => i18n( 'stats-images' ),
            'value' => [
                __number( $DB->query( '
                    SELECT  COUNT( _id ) AS cnt
                    FROM    ' . DB_PREFIX . 'image
                ' )->fetch_object()->cnt )
            ]
        ], [
            'icon' => 'airlines',
            'label' => i18n( 'stats-service' ),
            'value' => [
                __number( $DB->query( '
                    SELECT  COUNT( ICAO ) AS cnt
                    FROM    ' . DB_PREFIX . 'airport
                    WHERE   service = 1
                ' )->fetch_object()->cnt )
            ]
        ] ] ); ?>
    </div>
    <div class="stats-section">
        <h2 class="secondary-headline"><?php _i18n( 'stats-type' ); ?></h2>
        <p><?php _i18n( 'stats-type-desc' ); ?></p>
        <?php _stats_grid( [ [
            'icon' => 'public',
            'label' => i18n( 'airport-typep-large' ),
            'value' => [
                __number( AIRPORT_STATS['large'] )
            ]
        ], [
            'icon' => 'airplane_ticket',
            'label' => i18n( 'airport-typep-medium' ),
            'value' => [
                __number( AIRPORT_STATS['medium'] )
            ]
        ], [
            'icon' => 'flight',
            'label' => i18n( 'airport-typep-small' ),
            'value' => [
                __number( AIRPORT_STATS['small'] )
            ]
        ], [
            'icon' => 'mode_fan',
            'label' => i18n( 'airport-typep-heliport' ),
            'value' => [
                __number( AIRPORT_STATS['heliport'] )
            ]
        ], [
            'icon' => 'anchor',
            'label' => i18n( 'airport-typep-seaplane' ),
            'value' => [
                __number( AIRPORT_STATS['seaplane'] )
            ]
        ], [
            'icon' => 'landscape',
            'label' => i18n( 'airport-typep-altiport' ),
            'value' => [
                __number( AIRPORT_STATS['altiport'] )
            ]
        ] ] ); ?>
    </div>
</div>
<?php _footer(); ?>