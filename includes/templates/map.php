<?php

    add_resource( 'welcome', 'css', 'welcome.css' );

    _header();

    _map( [
        'save_position' => true,
        'save_type' => true
    ], 'full-screen full-overlay windbug' );

    $res_stats = airport_count( 'restriction' );

?>
<div class="welcome content-wide">
    <h1><?php _i18n( 'site-welcome-headline' ); ?></h1>
    <p><?php _i18n( 'site-welcome-text', __number( AIRPORT_ALL ) ); ?></p>
    <?php load_tpl_part( '_searchform' ); ?>
</div>
<div class="welcome-stats content-full">
    <a href="<?php _base_url( 'airports' ); ?>" class="stats-col">
        <div class="value"><?php echo __number( AIRPORT_STATS['large'] + AIRPORT_STATS['medium'] ); ?></div>
        <div class="label"><?php _i18n( 'stats-airport' ); ?></div>
    </a>
    <a href="<?php _base_url( 'airports/type/small' ); ?>" class="stats-col">
        <div class="value"><?php echo __number( AIRPORT_STATS['small'] ); ?></div>
        <div class="label"><?php _i18n( 'stats-airfield' ); ?></div>
    </a>
    <a href="<?php _base_url( 'airports/restriction/military' ); ?>" class="stats-col">
        <div class="value"><?php echo __number( $res_stats['military'] + $res_stats['joint_use'] ); ?></div>
        <div class="label"><?php _i18n( 'stats-airbase' ); ?></div>
    </a>
    <a href="<?php _base_url( 'airports/type/heliport' ); ?>" class="stats-col">
        <div class="value"><?php echo __number( AIRPORT_STATS['heliport'] ); ?></div>
        <div class="label"><?php _i18n( 'stats-heliport' ); ?></div>
    </a>
</div>
<?php _site_nav( [ null, [
        'i18n' => 'footer-nav-search-airports',
        'url' => $base . 'airports'
    ], [
        'i18n' => 'footer-nav-weather-forecast',
        'url' => $base . 'weather'
    ], [
        'i18n' => 'footer-nav-about',
        'url' => $base . 'about'
    ], null ], 'site-tabs content-wide welcome-tabs' ); ?>
<div class="welcome-text content-wide">
    <p><?php _i18n( 'about-goal-primary' ); ?></p>
    <p><?php _i18n( 'about-goal-secondary' ); ?></p>
</div>
<?php _footer(); ?>