<?php

    if( ( $country = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'country
        WHERE   code = "' . ( $path[2] ?? '' ) . '"
    ' ) )->num_rows == 0 ) {

        __404();

    }

    $country = $country->fetch_object();

    $list = $DB->query( '
        SELECT   r.code AS code,
                 r.name AS name,
                 COUNT( a.ICAO ) AS cnt
        FROM     ' . DB_PREFIX . 'region r,
                 ' . DB_PREFIX . 'airport a
        WHERE    r.country = "' . $country->code . '"
        AND      a.region = r.code
        GROUP BY r.code
        ORDER BY r.code ASC
    ' )->fetch_all( MYSQLI_ASSOC );

    $count = array_sum( array_column( $list, 'cnt' ) );

    $__site_canonical = $base . 'airports/country/' . $country->code;

    $__site_title = i18n( 'airports-country-title', $country->name, $country->code, $count );
    $__site_desc = i18n( 'airports-country-desc', $country->name, $country->code, $count );

    add_resource( 'region', 'css', 'region.css' );

    _header();

?>
<div class="region">
    <?php _map( [
        'type' => 'airport',
        'navaids' => true,
        'supress_sigmets' => false,
        'supress_day_night' => false,
        'query' => [
            'country' => $country->code
        ],
        'fit_bounds' => true
    ], 'minimal-ui' ); ?>
    <h1 class="primary-headline">
        <i class="icon">language</i>
        <span><?php echo $country->name; ?></span>
        <b><?php echo $count; ?></b>
    </h1>
</div>
<?php _footer(); ?>