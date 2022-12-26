<?php

    $__static_files = [];

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

        _resources();

        ?>
            <link rel="canonical" href="<?php echo SITE . $__site_canonical; ?>" />
            <title><?php _i18n( $__site_title ?? 'site-title-default' ); ?> — Airportmap</title>
            <meta name="description" content="<?php _i18n( $__site_desc ?? 'site-desc-default' ); ?>" />
        <?php

    }

    function _site_end() {

        _resources();

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
            ) . '" class="' . $link['classes'] . ' ' . (
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

    function add_resource(
        string $resource,
        string $type,
        string $url
    ) {

        global $__static_files;

        $res_id = $type . '-' . $resource;

        if( !empty( $dir = [
                'css' => 'styles/',
                'js' => 'scripts/'
            ][ strtolower( trim( $type ) ) ] ?? null ) &&
            !array_key_exists(
                $res_id,
                $__static_files
            )
        ) {

            $file = stripos( $url, 'http' ) === false
                ? RESOURCE . $dir . $url
                : $url;

            $__static_files[ $res_id ] = [
                'css' => '<link rel="stylesheet" href="' . $file . '" id="' . $res_id . '" />',
                'js' => '<script type="text/javascript" src="' . $file . '" id="' . $res_id . '"></script>'
            ][ $type ];

        }

    }

    function _resources() {

        global $__static_files;

        foreach( $__static_files as $res_id => $content ) {

            if( !empty( $content ) ) {

                echo $content;

                $__static_files[ $res_id ] = null;

            }

        }

    }

    function _header() {

        load_tpl_part( '_header' );

    }

    function _footer() {

        load_tpl_part( '_footer' );

    }

?>