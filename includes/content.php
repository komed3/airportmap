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

    function __redirect(
        string $to
    ) {

        header( 'Location: ' . SITE . $to );

        exit;

    }

    function __404() {

        __redirect( '404' );

    }

    function load_tpl_part(
        string $tpl,
        string $fallback = ''
    ) {

        if( is_readable( TEMPLATE . $tpl . '.php' ) ) {

            include TEMPLATE . $tpl . '.php';

        } else if( is_readable( TEMPLATE . $fallback . '.php' ) ) {

            include TEMPLATE . $fallback . '.php';

        }

    }

    function _site_header() {

        global $__site_canonical, $__site_title, $__site_desc;

        _resources();

        ?>
            <link rel="canonical" href="<?php echo SITE . $__site_canonical; ?>" />
            <title><?php echo $__site_title ?? i18n( 'site-title-default' ); ?> — Airportmap</title>
            <meta name="description" content="<?php echo ( $__site_desc ?? i18n( 'site-desc-default' ) ); ?>" />
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

            return $link ? '<a href="' . (
                $link['external'] ?? base_url( $link['url'] ?? '' )
            ) . '" class="' . $link['classes'] . ' ' . (
                ( $link['check'] ?? '' ) == $equal ? 'current' : ''
            ) . '">
                <span>' . ( $link['text'] ?? i18n( $link['i18n'] ?? '' ) ) . '</span>
            </a>' : '<div class="empty"></div>';

        }, $links ) ) . '</nav>';

    }

    function _site_nav(
        array $links = [],
        string $classes = '',
        int $check_path = 0
    ) {

        echo site_nav( $links, $classes, $check_path );

    }

    function pagination(
        int $results,
        int $page = 1,
        string $baseurl = '',
        int $per_page = 24
    ) {

        $maxpage = ceil( $results / 24 );
        $baseurl = SITE . $baseurl . '/';
        $pagelinks = [];
        $latest = 0;

        foreach( array_filter( array_unique( [
            1,
            max( 1, $page - 2 ),
            max( 1, $page - 1 ),
            $page,
            min( $maxpage, $page + 1 ),
            min( $maxpage, $page + 2 ),
            $maxpage
        ] ) ) as $pageno ) {

            if( $pageno > $latest + 1 ) {

                $pagelinks[] = '<span class="dots"><span>…</span></span>';

            }

            $pagelinks[] = $pageno == $page
                ? '<span class="curr"><span>' . $pageno . '</span></span>'
                : '<a class="link" href="' . $baseurl . $pageno . '"><span>' . $pageno . '</span></a>';

            $latest = $pageno;

        }

        if( count( $pagelinks ) <= 1 ) {

            $pagelinks = [];

        }

        return '<div class="pagination">
            <div class="pagelinks">
                ' . implode( '', $pagelinks ) . '
            </div>
            <div class="results">
                ' . i18n( 'search-results', $results, $page, $maxpage ) . '
            </div>
        </div>';

    }

    function _pagination(
        int $results,
        int $page = 1,
        string $baseurl = '',
        int $per_page = 24
    ) {

        echo pagination( $results, $page, $baseurl, $per_page );

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