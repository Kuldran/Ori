<?php

declare(strict_types=1);

namespace Origin\Utilities\Bucket;

trait Any {
    public function Any($value = NULL) {
        return $this->Bucket(NULL, $value);
    }
}
