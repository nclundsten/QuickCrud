<?php

namespace Crud\Middleware;

class CrudRouteMiddlewareFactory
{

    public function __invoke($container)
    {
        return new CrudRouteMiddleware($container);
    }
}