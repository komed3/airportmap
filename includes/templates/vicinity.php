<?php

    $lat = (float) ( $path[1] ?? 40.7 );
    $_lat = __DMS( $lat, 'lat' );

    $lon = (float) ( $path[2] ?? -74 );
    $_lon = __DMS( $lon, 'lon' );

    $__site_canonical = $base . 'vicinity';

    $__site_title = i18n( 'vicinity-title-at', $_lat, $_lon );
    $__site_desc = i18n( 'vicinity-desc' );

    add_resource( 'vicinity', 'css', 'vicinity.css' );

    _header();

?>
<div class="content-full vicinity">
    <?php _map( [
        'type' => 'airport',
        'navaids' => true,
        'waypoints' => true,
        'supress_sigmets' => true,
        'supress_day_night' => true,
        'position' => [
            'lat' => $lat,
            'lon' => $lon,
            'zoom' => 10
        ]
    ], 'minimal-ui' ); ?>
    <form class="vicinityform" data-form="vicinity" autocomplete="off">
        <span class="label"><?php _i18n( 'vicinity-label' ); ?></span>
        <input class="coord" type="number" name="lat" min="-90" max="90" step="0.00001" placeholder="<?php _i18n( 'latitude' ); ?>" value="<?php
            echo number_format( $lat, 5 ); ?>" />
        <input class="coord" type="number" name="lon" min="-180" max="180" step="0.00001" placeholder="<?php _i18n( 'longitude' ); ?>" value="<?php
            echo number_format( $lon, 5 ); ?>" />
        <button type="submit" name="vicinitysubmit" title="<?php _i18n( 'vicinity-submit-title' ); ?>">
            <i class="icon">near_me</i>
            <span><?php _i18n( 'vicinity-submit' ); ?></span>
        </button>
        <button type="submit" name="vicinitymy" title="<?php _i18n( 'vicinity-my-title' ); ?>">
            <i class="icon">my_location</i>
            <span><?php _i18n( 'vicinity-my' ); ?></span>
        </button>
    </form>
    <h1 class="primary-headline">
        <i class="icon">share_location</i>
        <span><?php _i18n( 'vicinity-title' ); ?></span>
        <b><?php echo $_lat; ?></b>
        <b><?php echo $_lon; ?></b>
    </h1>
    ...
</div>
<?php _footer(); ?>