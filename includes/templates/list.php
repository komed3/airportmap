<?php

    $letter = substr( strtoupper( trim( $path[1] ?? '*' ) ), 0, 1 );

    if( strpos( '*#ABCDEFGHIJKLMNOPQRSTUVWXYZ', $letter ) === false ) {

        __404();

    }

    $__site_canonical = 'list';

    $__site_title = i18n( 'list-title', $letter, $_count );
    $__site_desc = i18n( 'list-desc', $letter, $_count );

    _header();

?>

<?php _footer(); ?>