<?php

    $__site_search = base64_decode( $path[1] ?? '' );

    $results = airport_search( $__site_search );

    $__site_canonical = $base . 'search';

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
    <div class="search-results content-normal"></div>
</div>
<?php _footer(); ?>