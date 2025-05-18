<?php

declare(strict_types=1);

namespace Origin\Router;

interface iRouterModule {
    public function Precall($class, $route, array $parameters, array $request);
}