<?php

    $path[1] = $path[1] ?? 'all';
    $letter = strtoupper( trim( $path[1] ) );

    if( $path[1] == 'all' ) {

        $query = '1';

        $__site_canonical = 'list/all';

        $__site_title = i18n( 'list-title' );
        $__site_desc = i18n( 'list-desc' );

    } else if( strlen( $letter ) == 1 && strpos(
        '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        $letter
    ) !== false ) {

        $query = ' ICAO LIKE "' . $letter . '%" ';

        $__site_canonical = 'list/' . $letter;

        $__site_title = i18n( 'list-letter-title', $letter );
        $__site_desc = i18n( 'list-letter-desc', $letter );

    } else {

        __404();

    }

    $airports = $DB->query( '
        SELECT   *
        FROM     ' . DB_PREFIX . 'airport
        WHERE    ' . $query . '
        ORDER BY ICAO ASC
    ' )->fetch_all( MYSQLI_ASSOC );

    $count = count( $airports );
    $_count = __number( $count );

    _header();

?>
<div class="content-full list">
    <?php _site_nav(
        array_merge( [ [
            'i18n' => 'list-all',
            'url' => 'list/all',
            'check' => 'all'
        ] ], array_map( function( $l ) {
            return [
                'text' => $l,
                'url' => 'list/' . $l,
                'check' => $l
            ];
        }, str_split(
            '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'
        ) ) ),
        'site-tabs content-normal', 1
    ); ?>
    <h1 class="primary-headline">
        <i class="icon">tag</i>
        <span><?php echo $__site_title; ?></span>
        <b><?php echo $_count; ?></b>
    </h1>
    <div class="content-normal">
        <?php _airport_list(
            $airports, $path[2] ?? 1,
            'list/' . $path[1]
        ); ?>
    </div>
</div>
<?php _footer(); ?>