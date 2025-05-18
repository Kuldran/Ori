<?php

declare(strict_types=1);

namespace Origin\Utilities\Bucket;

trait Bucket {
    protected $things = [];
    protected function Bucket($key = NULL, $value = NULL) {
        if ($key === NULL) {
            $key = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        }
        if ($value !== NULL) {
            $this->things[$key] = $value;
        }
        return (isset($this->things[$key]) ? $this->things[$key] : NULL);
    }
    
    public function Reset(array $key = []) {
        foreach ($key as $k) {
            if (isset($this->things[$k])) {
                unset($this->things[$k]);
            }
        }
    }
}
