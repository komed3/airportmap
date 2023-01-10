<?php

    if( empty( $airport = airport_by( 'ICAO', $path[1] ?? '' ) ) ||
        !check_tpl( 'airport_' . ( $path[2] ?? 'info' ) ) ) {

        __404();

    }

    $base = 'airport/' . $airport['ICAO'] . '/';

    $__site_canonical = $base . ( $path[2] = $path[2] ?? 'info' );

    $__site_title = i18n_save( 'airport-title-' . $path[2], $airport['ICAO'], $airport['name'] ) ??
        i18n( 'airport-title', $airport['ICAO'], $airport['name'] );

    add_resource( 'airport', 'css', 'airport.css' );

    _header();

    $weather = airport_weather( $airport );

    $tabs = [ [
        'i18n' => 'airport-info',
        'url' => $base . 'info',
        'check' => 'info'
    ], [
        'i18n' => 'airport-weather',
        'url' => $base . 'weather',
        'check' => 'weather'
    ], [
        'i18n' => 'airport-nearby',
        'url' => $base . 'nearby',
        'check' => 'nearby'
    ], [
        'i18n' => 'airport-radio',
        'url' => $base . 'radio',
        'check' => 'radio'
    ], [
        'i18n' => 'airport-runways',
        'url' => $base . 'runways',
        'check' => 'runways'
    ], null ];

    if( $airport['home'] ) {

        $tabs[] = [
            'i18n' => 'link-home',
            'external' => $airport['home']
        ];

    }

    if( $airport['wiki'] ) {

        $tabs[] = [
            'i18n' => 'link-wiki',
            'external' => 'https://wikipedia.org/wiki/' . $airport['wiki']
        ];

    }

?>
<div class="content-full airport">
    <?php if( $image = airport_image( $airport['ICAO'] ) ) { ?>
        <div class="site-image" style="background-image: url( <?php echo $image['file']; ?> );">
            <div class="credits"><?php echo $image['credits']; ?></div>
        </div>
    <?php } ?>
    <h1 class="primary-headline airport-<?php echo $airport['type']; ?> restriction-<?php echo $airport['restriction']; ?> cat-<?php echo (
        $cat = ( $weather['flight_cat'] ?? 'UNK' )
    ); ?>">
        <mapicon invert></mapicon>
        <b><?php echo $airport['ICAO']; ?></b>
        <span class="name"><?php echo $airport['name']; ?></span>
        <span class="cat"><?php _i18n( 'cat-' . $cat ); ?></span>
    </h1>
    <?php _airport_warn( $airport ); ?>
    <?php _site_nav( $tabs, 'site-tabs content-normal', 2, '__tabs' ); ?>
    <?php echo load_tpl_part( 'airport_' . $path[2] ); ?>
</div>
<?php _footer(); ?>