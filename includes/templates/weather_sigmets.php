<?php

    $sigmets = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'sigmet
        WHERE   valid_from <= NOW()
        AND     valid_to >= NOW()
    ' )->fetch_all( MYSQLI_ASSOC );

    $__site_canonical = 'weather/sigmets';

    $__site_title = i18n( 'sigmets-title', count( $sigmets ) );
    $__site_desc = 'sigmets-desc';

    _header();

?>
<h1><?php echo $__site_title; ?></h1>
<?php _footer(); ?>