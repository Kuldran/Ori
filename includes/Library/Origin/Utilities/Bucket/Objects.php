<?php

declare(strict_types=1);

namespace Origin\Utilities\Bucket;


trait Objects {
    public function Objects($value = NULL) {
        return $this->Bucket(NULL, $value);
    }
}