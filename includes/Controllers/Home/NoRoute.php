<?php

declare(strict_types=1);

namespace Controllers\Home;

use \Origin\Utilities\Layout;

class NoRoute {
    public function Main() {
        Layout::Get()->Display('404.tpl');
    }
}