<?php

declare(strict_types=1);

namespace Origin\Utilities\Bucket;

use \Exception;

trait Blob {
    public function Blob($value = NULL) {
        if (($value !== NULL) && (!is_string($value))) {
            throw new Exception(sprintf('Invalid value specified for type %s.', __FUNCTION__));
        }
        return $this->Bucket(NULL, $value);
    }
}
