<?php

    $__site_canonical;

    $__site_title;
    $__site_desc;

    $__site_class = [ 'apm' ];

    function _base_url(
        string $url = ''
    ) {

        echo SITE . $url;

    }

    function load_tpl_part(
        string $tpl
    ) {

        if( is_readable( TEMPLATE . $tpl . '.php' ) ) {

            include TEMPLATE . $tpl . '.php';

        }

    }

    function site_header() {

        global $__site_canonical, $__site_title, $__site_desc;

        ?>
            <link rel="canonical" href="<?php echo SITE . $__site_canonical; ?>" />
            <title><?php _i18n( $__site_title ?? 'site-title-default' ); ?> — Airportmap</title>
            <meta name="description" content="<?php _i18n( $__site_desc ?? 'site-desc-default' ); ?>" />
        <?php

    }

    function site_class() {

        global $__site_class;

        ?>class="<?php echo implode( ' ', $__site_class ); ?>"<?php

    }

    function _header() {

        load_tpl_part( '_header' );

    }

    function _footer() {

        load_tpl_part( '_footer' );

    }

?>