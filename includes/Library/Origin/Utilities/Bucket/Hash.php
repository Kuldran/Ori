<?php

declare(strict_types=1);

namespace Origin\Utilities\Bucket;

use \Exception;

trait Hash {
    public function Hash($value = NULL) {
        if (($value !== NULL) && (!is_array($value))) {
            throw new Exception(sprint_f('Invalid value specified for type %s.', __FUNCTION__));
        }
        return $this->Bucket(NULL, $value);
    }
}
