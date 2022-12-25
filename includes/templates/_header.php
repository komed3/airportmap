<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="author" content="Paul Köhler (komed3)" />
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1" />
        <meta name="msapplication-TileColor" content="#603cba" />
        <meta name="theme-color" content="#ffffff" />
        <link rel="icon" type="image/png" sizes="48x48" href="<?php _base_url( 'favicon-48x48.png' ); ?>" />
        <link rel="icon" type="image/png" sizes="32x32" href="<?php _base_url( 'favicon-32x32.png' ); ?>" />
        <link rel="icon" type="image/png" sizes="16x16" href="<?php _base_url( 'favicon-16x16.png' ); ?>" />
        <link rel="apple-touch-icon" sizes="180x180" href="<?php _base_url( 'apple-touch-icon.png' ); ?>" />
        <link rel="mask-icon" href="<?php _base_url( 'safari-pinned-tab.svg' ); ?>" color="#7050aa" />
        <link rel="manifest" href="<?php _base_url( 'site.webmanifest' ); ?>" />
        <?php site_header(); ?>
    </head>
    <body <?php site_class(); ?>>
        <div id="wrapper">
            <div id="header">
                <a href="<?php _base_url(); ?>" title="<?php _i18n( 'site-title-default' ); ?>" class="site-logo">
                    <img src="<?php _base_url( 'favicon-48x48.png' ); ?>" alt="" />
                </a>
            </div>