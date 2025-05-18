<?php

declare(strict_types=1);

namespace Origin\Utilities\Bucket;

use \Exception;

trait Boolean {
    public function Boolean($value = NULL) {
        if (($value !== NULL) && (!is_bool($value))) {
            throw new Exception(sprint_f('Invalid value specified for type %s', __FUNCTION__));
        }
        
        return $this->Bucket(NULL, $value);
    }
}
