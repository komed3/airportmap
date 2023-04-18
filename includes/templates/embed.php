<?php

    $embed_code = $path[1] ?? 'KLAX';
    $embed_lang = $path[2] ?? i18n_locale();
    $embed_options = [
        'weather' => $path[3] ?? 1,
        'stats' => $path[4] ?? 1,
        'image' => $path[5] ?? 0
    ];

    $embed_url = API . 'embed.php?' . http_build_query( array_merge( [
        'airport' => $embed_code,
        'lang' => $embed_lang
    ], $embed_options ), '', '&' );

    $embed_title = airport_by( 'ICAO', $embed_code )['name'] ?? i18n( 'unknown' );

    add_resource( 'text', 'css', 'text.min.css' );
    add_resource( 'embedform', 'css', 'embedform.min.css' );

    $__site_canonical = 'embed';

    $__site_title = i18n( 'embed-title' );
    $__site_desc = i18n( 'embed-desc' );

    _header();

?>
<div class="content-normal embed text-content">
    <h1><?php _i18n( 'embed-title' ); ?></h1>
    <p class="first"><?php _i18n( 'embed-intro' ); ?></p>
    <p><?php _i18n( 'embed-usage', base_url( 'privacy' ) ); ?></p>
    <div class="embed-generator">
        <form class="embedform" data-form="embed" autocomplete="off">
            <h2><?php _i18n( 'embedform-title' ); ?></h2>
            <div class="formline">
                <label><?php _i18n( 'embedform-code' ); ?></label>
                <input type="text" name="code" value="<?php echo $embed_code; ?>" required />
            </div>
            <div class="formline">
                <label><?php _i18n( 'embedform-lang' ); ?></label>
                <select name="lang" required>
                    <?php foreach( LANGUAGES as $lng ) { ?>
                        <option value="<?php echo $lng; ?>" <?php if( $lng == $embed_lang ) { echo 'selected'; } ?>>
                            <?php _i18n( 'site-language-' . $lng ); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="formline">
                <label><?php _i18n( 'embedform-weather' ); ?></label>
                <input type="checkbox" name="weather" value="1" <?php
                    if( $embed_options['weather'] ) { ?>checked<?php }
                ?> id="__embedform_weather" />
                <label class="checkbox" for="__embedform_weather">
                    <i class="icon yes">done</i>
                    <i class="icon no">close</i>
                </label>
            </div>
            <div class="formline">
                <label><?php _i18n( 'embedform-stats' ); ?></label>
                <input type="checkbox" name="stats" value="1" <?php
                    if( $embed_options['stats'] ) { ?>checked<?php }
                ?> id="__embedform_stats" />
                <label class="checkbox" for="__embedform_stats">
                    <i class="icon yes">done</i>
                    <i class="icon no">close</i>
                </label>
            </div>
            <div class="formline">
                <label><?php _i18n( 'embedform-image' ); ?></label>
                <input type="checkbox" name="image" value="1" <?php
                    if( $embed_options['image'] ) { ?>checked<?php }
                ?> id="__embedform_image" />
                <label class="checkbox" for="__embedform_image">
                    <i class="icon yes">done</i>
                    <i class="icon no">close</i>
                </label>
            </div>
            <div class="formsubmit">
                <button type="submit" name="embedsubmit">
                    <span><?php _i18n( 'embedform-submit' ); ?></span>
                </button>
            </div>
        </form>
        <textarea class="embedcode" readonly><iframe width="400" height="600" src="<?php
            echo $embed_url;
        ?>" title="<?php
            echo $embed_title;
        ?>"></iframe></textarea>
    </div>
    <p><?php _i18n( 'embed-test' ); ?></p>
    <iframe class="embediframe <?php
        if( $embed_options['image'] ) { ?>image<?php }
    ?>" src="<?php echo $embed_url; ?>" title="<?php echo $embed_title; ?>"></iframe>
</div>
<?php _footer(); ?>