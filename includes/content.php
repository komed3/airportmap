<?php

    $__static_files = [];

    $__site_canonical;

    $__site_title;
    $__site_desc;

    $__site_classes = [ 'apm' ];

    $__site_search = '';

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

    function check_tpl(
        string $tpl
    ) {

        return is_readable( TEMPLATE . $tpl . '.php' );

    }

    function load_tpl_part(
        string $tpl,
        string $fallback = ''
    ) {

        if( check_tpl( $tpl ) ) {

            include TEMPLATE . $tpl . '.php';

        } else if( check_tpl( $fallback ) ) {

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
            <meta name="robots" content="all, noimageindex" />
        <?php

    }

    function _site_end() {

        _resources();

    }

    function _site_classes() {

        global $__site_classes;

        echo 'class="' . implode( ' ', $__site_classes ) . '"';

    }

    function get_file(
        string $file,
        string $size = ''
    ) {

        return RESOURCE . 'images/' . $size . $file;

    }

    function _get_file(
        string $file,
        string $size = ''
    ) {

        echo get_file( $file, $size );

    }

    function site_nav(
        array $links = [],
        string $classes = '',
        int $check_path = 0,
        $jump_to = null
    ) {

        global $path;

        $equal = $path[ $check_path ] ?? '';

        return ( $jump_to ? '<a name="' . $jump_to . '" class="anchor" tabindex="-1"></a>' : '' ) . '
        <nav class="' . $classes . '">' .
            implode( '', array_map( function( $link ) use ( $equal, $jump_to ) {

                return $link ? '<a href="' . (
                    $link['external'] ?? base_url( $link['url'] ?? '' )
                ) . (
                    $jump_to ? '#' . $jump_to : ''
                ) . '" class="' . $link['classes'] . ' ' . (
                    ( $link['check'] ?? '' ) == $equal ? 'current' : ''
                ) . '" target="' . (
                    $link['external'] ? '_blank' : '_self'
                ) . '">
                    <span>' . ( $link['text'] ?? i18n( $link['i18n'] ?? '' ) ) . '</span>
                </a>' : '<div class="empty"></div>';

            }, $links ) ) . '
        </nav>';

    }

    function _site_nav(
        array $links = [],
        string $classes = '',
        int $check_path = 0,
        $jump_to = null
    ) {

        echo site_nav( $links, $classes, $check_path, $jump_to );

    }

    function pagination(
        int $results,
        int $page = 1,
        string $baseurl = '',
        int $per_page = 24,
        $jump_to = '_pagination'
    ) {

        $maxpage = ceil( $results / $per_page );
        $baseurl = base_url( $baseurl . '/' );
        $pagelinks = $pageselect = [];
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

                $pageselect[] = '<option disabled>…</option>';

            }

            $pagelinks[] = $pageno == $page
                ? '<span class="curr">
                       <span>' . __number( $pageno ) . '</span>
                   </span>'
                : '<a class="link" href="' . $baseurl . $pageno . ( $jump_to ? '#' . $jump_to : '' ) . '">
                       <span>' . __number( $pageno ) . '</span>
                   </a>';

            $pageselect[] = '<option value="' . $pageno . '" ' . ( $pageno == $page ? 'selected' : '' ) . '>
                ' . __number( $pageno ) . '
            </option>';

            $latest = $pageno;

        }

        if( count( $pagelinks ) <= 1 ) {

            $pagelinks = [];

        }

        return ( $jump_to ? '<a name="' . $jump_to . '" class="anchor" tabindex="-1"></a>' : '' ) . '
        <div class="pagination">
            <select data-action="select-page" data-base="' . $baseurl . '" data-jump="' . $jump_to . '">
                ' . implode( '', $pageselect ) . '
            </select>
            <div class="pagelinks">
                ' . implode( '', $pagelinks ) . '
            </div>
            <div class="results">
                ' . i18n( 'search-results', __number( $results ), __number( $page ), __number( $maxpage ) ) . '
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

    function back_to(
        string $page,
        string $name,
        string $classes = ''
    ) {

        return '<div class="backto ' . $classes . '">
            <a href="' . base_url( $page ) . '">
                <i class="icon">arrow_back</i>
                <span>' . $name . '</span>
            </a>
        </div>';

    }

    function _back_to(
        string $page,
        string $name,
        string $classes = ''
    ) {

        echo back_to( $page, $name, $classes );

    }

    function pagelist(
        string $page,
        array $results
    ) {

        $list = '';

        foreach( $results as $row ) {

            $list .= '<div><a href="' . base_url( $page . '/' . $row['page'] ) . '">
                <span>' . ( $row['name'] ?? i18n( 'unknown' ) ) . '</span>
                <b>(' . __number( $row['cnt'] ) . ')</b>
            </a></div>';

        }

        return '<div class="pagelist">
            ' . $list . '
        </div>';

    }

    function _pagelist(
        string $page,
        array $results
    ) {

        echo pagelist( $page, $results );

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
                (array) $__static_files
            )
        ) {

            $file = stripos( $url, 'http' ) === false
                ? RESOURCE . $dir . str_replace( '.css', '.min.css', $url )
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