<?php

    $__site_canonical;

    $__site_title;
    $__site_desc;

    $__site_classes = [ 'apm' ];

    function base_url(
        string $url = ''
    ) {

        return SITE . $url;

    }

    function _base_url(
        string $url = ''
    ) {

        echo base_url( $url );

    }

    function load_tpl_part(
        string $tpl
    ) {

        if( is_readable( TEMPLATE . $tpl . '.php' ) ) {

            include TEMPLATE . $tpl . '.php';

        }

    }

    function _site_header() {

        global $__site_canonical, $__site_title, $__site_desc;

        ?>
            <link rel="canonical" href="<?php echo SITE . $__site_canonical; ?>" />
            <title><?php _i18n( $__site_title ?? 'site-title-default' ); ?> — Airportmap</title>
            <meta name="description" content="<?php _i18n( $__site_desc ?? 'site-desc-default' ); ?>" />
        <?php

    }

    function _site_classes() {

        global $__site_classes;

        echo 'class="' . implode( ' ', $__site_classes ) . '"';

    }

    function site_nav(
        array $links = [],
        string $classes = '',
        int $check_path = 0
    ) {

        global $path;

        $equal = $path[ $check_path ] ?? '';

        return '<nav class="' . $classes . '">' . implode( '', array_map( function( $link ) use ( $equal ) {

            return '<a href="' . (
                $link['external'] ?? base_url( $link['url'] ?? '' )
            ) . '" class="' . (
                ( $link['check'] ?? '' ) == $equal ? 'current' : ''
            ) . '">
                <span>' . ( $link['text'] ?? i18n( $link['i18n'] ?? '' ) ) . '</span>
            </a>';

        }, $links ) ) . '</nav>';

    }

    function _site_nav(
        array $links = [],
        string $classes = '',
        int $check_path = 0
    ) {

        echo site_nav( $links, $classes, $check_path );

    }

    function _header() {

        load_tpl_part( '_header' );

    }

    function _footer() {

        load_tpl_part( '_footer' );

    }

?>