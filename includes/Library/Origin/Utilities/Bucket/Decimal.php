<?php

declare(strict_types=1);

namespace Origin\Utilities\Bucket;
trait Decimal {
    public function Decimal($value = NULL) {
        if (($value !== NULL) && (!is_float($value) && (!is_numeric($value)))) {
            throw new \Exception(sprintf('Invalid value specified for type %s - %s.', __FUNCTION__, $value));
        }
        
        return $this->Bucket(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['function'], $value);
    }
}
