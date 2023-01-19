<?php

    $__i18n_locale = $_COOKIE['locale'] ?? LOCALE;
    $__i18n_msg = [];

    function i18n(
        string $msgkey,
        ...$replaces
    ) {

        global $__i18n_msg;

        $key = trim( strtolower( $msgkey ) );
        $msg = array_key_exists( $key, $__i18n_msg )
            ? $__i18n_msg[ $key ] : '›' . $msgkey . '‹';

        foreach( $replaces as $idx => $replace ) {

            $msg = str_replace( '$' . ( $idx + 1 ), $replace, $msg );

        }

        return $msg;

    }

    function i18n_save(
        string $msgkey,
        ...$replaces
    ) {

        return strpos(
            $msg = i18n( $msgkey, ...$replaces ), '›'
        ) === 0 ? null : $msg;

    }

    function _i18n(
        string $msgkey,
        ...$replaces
    ) {

        echo i18n( $msgkey, ...$replaces );

    }

    function i18n_load(
        string $lang = '',
        bool $set_cookie = false
    ) {

        global $__i18n_locale, $__i18n_msg;

        $load_lang = $__i18n_locale;

        if( strlen( $lang ) && is_readable( LANG . $lang . '.json' ) ) {

            $load_lang = $lang;

            if( !!( $_COOKIE['cookie_test'] ?? 0 ) && $set_cookie ) {

                setcookie( 'locale', $load_lang, COOKIE_EXP );

            }

        }

        $__i18n_msg = json_decode(
            file_get_contents( LANG . $load_lang . '.json' ),
            true
        );

    }

    function i18n_locale() {

        global $__i18n_locale;

        return $__i18n_locale;

    }

    function _i18n_locale() {

        echo i18n_locale();

    }

?>