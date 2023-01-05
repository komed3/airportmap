<?php

    if( ( $country = $DB->query( '
        SELECT  *
        FROM    ' . DB_PREFIX . 'country
        WHERE   code = "' . ( $path[2] ?? '' ) . '"
    ' ) )->num_rows == 0 ) {

        __404();

    }

    $country = $country->fetch_object();

    $__site_canonical = $base . 'airports/country/' . $country->code;

    $__site_title = i18n( 'airports-country-title', $country->name, $country->code );
    $__site_desc = i18n( 'airports-country-desc', $country->name, $country->code );

    _header();

?>
<div class="airports">
    <?php _map( [], 'ui-minimal' ); ?>
    <h1 class="primary-headline">
        <i class="icon">language</i>
        <span><?php echo $country->name; ?></span>
        <b></b>
    </h1>
</div>
<?php _footer(); ?>