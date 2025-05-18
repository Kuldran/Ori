<?php

declare(strict_types=1);

namespace Origin\Utilities\Types;

class Hash extends \ArrayObject {
    public function Load(?array $array = NULL) {
        if ($array === NULL) {
            throw new Exception('Attempted to populate an Hash with nothing.');
        }

        return $this->CreateChildren($array);
    }

    private function CreateChildren(array $array) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $tmp = new Hash();
                $tmp->Load($value);
                $this->offsetSet($key, $tmp);
            } else {
                $this->offsetSet($key, $value);
            }
        }

        return TRUE;
    }
}
