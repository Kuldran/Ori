<?php

declare(strict_types=1);

namespace Origin\Utilities\Bucket;

trait Number {
    public function Number($value = NULL) {
        if (($value !== NULL) && (!is_numeric($value))) {
            throw new \Exception(sprint_f('Invalid value specified for type %s.', __FUNCTION__));
        }
        return $this->Bucket(NULL, $value);
    }
}
