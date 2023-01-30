<!DOCTYPE html>
<html lang="<?php _i18n_locale(); ?>">
    <head>
        <meta charset="UTF-8" />
        <meta name="author" content="Paul Köhler (komed3)" />
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1" />
        <meta name="msapplication-TileColor" content="#603cba" />
        <meta name="theme-color" content="#ffffff" />
        <link rel="icon" href="<?php _base_url( 'favicon.ico' ); ?>" />
        <link rel="icon" type="image/png" sizes="48x48" href="<?php _base_url( 'favicon-48x48.png' ); ?>" />
        <link rel="icon" type="image/png" sizes="32x32" href="<?php _base_url( 'favicon-32x32.png' ); ?>" />
        <link rel="icon" type="image/png" sizes="16x16" href="<?php _base_url( 'favicon-16x16.png' ); ?>" />
        <link rel="apple-touch-icon" sizes="180x180" href="<?php _base_url( 'apple-touch-icon.png' ); ?>" />
        <link rel="mask-icon" href="<?php _base_url( 'safari-pinned-tab.svg' ); ?>" color="#7050aa" />
        <link rel="manifest" href="<?php _base_url( 'site.webmanifest' ); ?>" />
        <?php _site_header(); ?>
    </head>
    <body <?php _site_classes(); ?>>
        <div id="wrapper">
            <div id="header">
                <a href="<?php _base_url(); ?>" title="<?php _i18n( 'site-title-default' ); ?>" class="site-logo">
                    <img src="<?php _base_url( 'favicon-48x48.png' ); ?>" alt="" />
                </a>
                <?php _site_nav( [ [
                    'i18n' => 'site-nav-map',
                    'url' => '',
                    'check' => ''
                ], [
                    'i18n' => 'site-nav-airports',
                    'url' => 'airports',
                    'check' => 'airports'
                ], [
                    'i18n' => 'site-nav-weather',
                    'url' => 'weather',
                    'check' => 'weather'
                ], [
                    'i18n' => 'site-nav-list',
                    'url' => 'list',
                    'check' => 'list'
                ], [
                    'i18n' => 'site-nav-traffic',
                    'url' => 'traffic',
                    'check' => 'traffic'
                ] ], 'site-nav' ); ?>
                <?php load_tpl_part( '_searchform' ); ?>
            </div>
            <div id="content">