<?php

    function sigmet_hazard(
        array $sigmet
    ) {

        return implode( ' ', array_filter( [
            i18n_save( 'qualifier-' . $sigmet['qualifier'] ),
            i18n_save( 'hazard-' . $sigmet['hazard'] )
        ] ) );

    }

?>