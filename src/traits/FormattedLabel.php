<?php

namespace shaqman\addition\traits;

trait FormattedLabel {

    public function getFormattedLabel($delimiter = '<br />') {
        return str_ireplace("\\n", $delimiter, $this->label);
    }

}
