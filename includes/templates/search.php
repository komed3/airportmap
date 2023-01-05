<?php

    $__site_search = base64_decode( $path[1] ?? '' );

    $__site_canonical = $base . 'search';

    $__site_title = i18n( 'search-title', $__site_search );
    $__site_desc = i18n( 'search-desc' );

    _header();

    _footer();

?>