<?php

declare(strict_types=1);

namespace Controllers\Home;

use \Origin\Utilities\Layout;

class Index {
    public function Main() {
        Layout::Get()->Display('index.tpl');
    }
}