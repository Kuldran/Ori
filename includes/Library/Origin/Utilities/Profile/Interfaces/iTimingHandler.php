<?php

declare(strict_types=1);

namespace Origin\Utilities\Profile\Interfaces;

interface iTimingHandler extends \JsonSerializable {
    public function Start();
    public function Stop();
    public function Name($name = NULL);
    public function File($file = NULL);
    public function Line($line = NULL);
    public function Stack();
    public function Difference();
    public function Format();
}