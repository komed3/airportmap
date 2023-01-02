<?php

    if( empty( $airport = airport_by( 'ICAO', $path[1] ?? '' ) ) ) {

        __404();

    }

    $__site_canonical = 'airport/' . $airport['ICAO'];

    $__site_title = i18n( 'airport-title', $airport['ICAO'], $airport['name'] );

    _header();

?>
<div class="content-full airport">
    <?php if( $image = airport_image( $airport['ICAO'] ) ) { ?>
        <div class="site-image" style="background-image: url( <?php echo $image['file']; ?> );">
            <div class="credits"><?php echo $image['credits']; ?></div>
        </div>
    <?php } ?>
    <h1 class="primary-headline">
        <b><?php echo $airport['ICAO']; ?></b>
        <span><?php echo $airport['name']; ?></span>
    </h1>
</div>
<?php _footer(); ?>