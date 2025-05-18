<?php


declare(strict_types=1);

namespace Origin\Utilities\Bucket;


trait Date {
    public function Date(?\DateTime $value = NULL) {
        return $this->Bucket(NULL, $value);
    }
}
