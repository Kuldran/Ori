<?php

declare(strict_types=1);

namespace Origin\Utilities\Bucket;

trait ToArray {
    public function ToArray() {
        return $this->things;
    }
}
