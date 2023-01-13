<?php

    function _stats_grid(
        array $raw = []
    ) {

        $boxes = [];

        foreach( $raw as $row ) {

            $boxes[] = '<div class="stats-box">
                <i class="icon">' . $row['icon'] . '</i>
                <div class="info">
                    <div class="label">
                        ' . $row['label'] . '
                    </div>
                    <div class="value">
                        <span>' . implode( '</span><span>', $row['value'] ) . '</span>
                    </div>
                    ' . ( array_key_exists( 'link', $row ) ? '<div class="link">
                        ' . $row['link'] . '
                    </div>' : '' ) . '
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

    /* stats */

    $res_count = airport_count( 'restriction' );

    $super = [];

    if( $res = $DB->query( '
        SELECT   ICAO, name, alt
        FROM     ' . DB_PREFIX . 'airport
        WHERE    type IN ( "large", "medium" )
        ORDER BY alt DESC
        LIMIT    0, 1
    ' )->fetch_assoc() ) {

        $super[] = [
            'icon' => 'landscape',
            'label' => i18n( 'stats-super-heighest' ),
            'value' => [
                alt_in( $res['alt'], 'ft' ),
                '(' . alt_in( $res['alt'] / 3.281, 'm' ) . ')'
            ],
            'link' => airport_link( $res )
        ];

    }

    if( $res = $DB->query( '
        SELECT   ICAO, name, alt
        FROM     ' . DB_PREFIX . 'airport
        WHERE    type IN ( "large", "medium" )
        ORDER BY alt ASC
        LIMIT    0, 1
    ' )->fetch_assoc() ) {

        $super[] = [
            'icon' => 'layers',
            'label' => i18n( 'stats-super-lowest' ),
            'value' => [
                alt_in( $res['alt'], 'ft' ),
                '(' . alt_in( $res['alt'] / 3.281, 'm' ) . ')'
            ],
            'link' => airport_link( $res )
        ];

    }

    $super[] = [
        'icon' => 'explore',
        'label' => i18n( 'stats-super-remotest' ),
        'value' => [
            alt_in( 2030, 'nm' ),
            '(' . alt_in( 3760, 'km' ) . ')'
        ],
        'link' => airport_link( airport_by( 'ICAO', 'SCIP' ) )
    ];

    if( $res = $DB->query( '
        SELECT   a.ICAO, a.name, r.length
        FROM     ' . DB_PREFIX . 'airport a,
                 ' . DB_PREFIX . 'runway r
        WHERE    r.airport = a.ICAO
        AND      a.service = 1
        AND      a.type IN ( "large", "medium", "small" )
        AND      a.restriction = "public"
        AND      r.inuse = 1
        AND      r.ident NOT LIKE "%H%"
        AND      r.length IS NOT NULL
        ORDER BY r.length DESC
        LIMIT    0, 1
    ' )->fetch_assoc() ) {

        $super[] = [
            'icon' => 'flight_takeoff',
            'label' => i18n( 'stats-super-longest' ),
            'value' => [
                alt_in( $res['length'], 'ft' ),
                '(' . alt_in( $res['length'] / 3.281, 'm' ) . ')'
            ],
            'link' => airport_link( $res )
        ];

    }

    if( $res = $DB->query( '
        SELECT   a.ICAO, a.name, r.length
        FROM     ' . DB_PREFIX . 'airport a,
                 ' . DB_PREFIX . 'runway r
        WHERE    r.airport = a.ICAO
        AND      a.service = 1
        AND      a.type IN ( "large", "medium", "small" )
        AND      a.restriction = "public"
        AND      r.inuse = 1
        AND      r.ident NOT LIKE "%H%"
        AND      r.length IS NOT NULL
        ORDER BY r.length ASC
        LIMIT    0, 1
    ' )->fetch_assoc() ) {

        $super[] = [
            'icon' => 'warning',
            'label' => i18n( 'stats-super-shortest' ),
            'value' => [
                alt_in( $res['length'], 'ft' ),
                '(' . alt_in( $res['length'] / 3.281, 'm' ) . ')'
            ],
            'link' => airport_link( $res )
        ];

    }

    if( $res = $DB->query( '
        SELECT   a.ICAO, a.name, r.slope, r.vertical
        FROM     ' . DB_PREFIX . 'airport a,
                 ' . DB_PREFIX . 'runway r
        WHERE    r.airport = a.ICAO
        AND      a.type NOT IN ( "heliport", "seaplane", "closed" )
        AND      a.restriction = "public"
        AND      r.inuse = 1
        AND      r.ident NOT LIKE "%H%"
        AND      r.slope IS NOT NULL
        ORDER BY r.slope DESC
        LIMIT    0, 1
    ' )->fetch_assoc() ) {

        $super[] = [
            'icon' => 'landslide',
            'label' => i18n( 'stats-super-steepest' ),
            'value' => [
                __number( $res['slope'] ) . '&#8239;%',
                '(' . alt_in( $res['vertical'], 'ft' ) . ')'
            ],
            'link' => airport_link( $res )
        ];

    }

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
        <h2 class="secondary-headline"><?php _i18n( 'stats-super' ); ?></h2>
        <p><?php _i18n( 'stats-super-desc' ); ?></p>
        <?php _stats_grid( $super ); ?>
    </div>
    <div class="stats-section">
        <h2 class="secondary-headline"><?php _i18n( 'stats-type' ); ?></h2>
        <p><?php _i18n( 'stats-type-desc' ); ?></p>
        <?php _stats_grid( [ [
            'icon' => 'luggage',
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
        ], [
            'icon' => 'public',
            'label' => i18n( 'airport-resp-public' ),
            'value' => [
                __number( $res_count['public'] )
            ]
        ], [
            'icon' => 'military_tech',
            'label' => i18n( 'airport-resp-military' ),
            'value' => [
                __number( $res_count['military'] )
            ]
        ], [
            'icon' => 'lock_open',
            'label' => i18n( 'airport-resp-private' ),
            'value' => [
                __number( $res_count['private'] )
            ]
        ] ] ); ?>
    </div>
</div>
<?php _footer(); ?>