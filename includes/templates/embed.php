<?php

    $embed_code = $path[1] ?? 'KLAX';
    $embed_lang = $path[2] ?? 'en';

    add_resource( 'text', 'css', 'text.css' );
    add_resource( 'embedform', 'css', 'embedform.css' );

    $__site_canonical = $base . 'embed';

    $__site_title = i18n( 'embed-title' );
    $__site_desc = i18n( 'embed-desc' );

    _header();

?>
<div class="content-normal embed text-content">
    <h1><?php _i18n( 'embed-title' ); ?></h1>
    <p class="first"><?php _i18n( 'embed-intro' ); ?></p>
    <p><?php _i18n( 'embed-usage', base_url( 'privacy' ) ); ?></p>
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
        <div class="formsubmit">
            <button type="submit" name="embedsubmit">
                <span><?php _i18n( 'embedform-submit' ); ?></span>
            </button>
        </div>
    </form>
</div>
<?php _footer(); ?>