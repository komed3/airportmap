<?php global $__site_search; ?>
<form class="searchform" data-form="search" autocomplete="off">
    <input class="searchtext" type="text" name="searchtext" placeholder="<?php _i18n( 'searchform-placeholder' ); ?>" value="<?php echo $__site_search; ?>" />
    <button type="submit" name="searchsubmit" title="<?php _i18n( 'searchform-submit-title' ); ?>">
        <i class="icon">send</i>
    </button>
</form>