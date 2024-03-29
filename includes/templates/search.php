<?php

    $__site_search = urldecode( $path[1] ?? '' );

    $results = airport_search( $__site_search );

    if( count( $results ) == 1 && strpos( $_SERVER['HTTP_REFERER'], 'search' ) === false ) {

        __redirect( 'airport/' . $results[0]['ICAO'] );

    }

    $__site_canonical = 'search';

    $__site_title = i18n( 'search-title', $__site_search, count( $results ) );
    $__site_desc = i18n( 'search-desc' );

    _header();

?>
<div class="search">
    <div class="search-form content-normal">
        <?php load_tpl_part( '_searchform' ); ?>
    </div>
    <h1 class="primary-headline">
        <i class="icon">near_me</i>
        <span><?php _i18n( 'search-for' ); ?></span>
        <b><?php echo $__site_search; ?></b>
    </h1>
    <div class="search-results content-normal">
        <?php _airport_list( $results, $path[2] ?? 1, 'search/' . $path[1] ); ?>
    </div>
</div>
<?php _footer(); ?>