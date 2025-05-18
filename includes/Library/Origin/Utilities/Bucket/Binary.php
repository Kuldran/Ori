<?php

declare(strict_types=1);

namespace Origin\Utilities\Bucket;

trait Binary {
    public function Binary($value = NULL) {
        return $this->Bucket(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['function'], $value);
    }
}
