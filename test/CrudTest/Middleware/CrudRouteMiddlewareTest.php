<?php

namespace CrudTest\Middleware;

use Crud\Middleware\CrudRouteMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;

class CrudRouteMiddlewareTest extends TestCase
{
    public function testProcessWithForm()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $routeResult = $this->prophesize(RouteResult::class);
        $route = $this->prophesize(Route::class);
        $container = $this->prophesize(ContainerInterface::class);
        $options = ['form' => SomeForm::class];
        $handler = $this->prophesize(RequestHandlerInterface::class);

        $form = new \Zend\Form\Form;

        $request->getAttribute(RouteResult::class)->willReturn($routeResult);
        $routeResult->getMatchedRoute()->willReturn($route);
        $route->getOptions()->willReturn($options);
        $container->get(SomeForm::class)->willReturn($form)->shouldBeCalled();

        $request->withAttribute(
            CrudRouteMiddleware::CRUD_CONFIG,
            ['form' => $form]
        )->shouldBeCalled()
        ->willReturn($request);

        $handler->handle($request->reveal());

        $middleware = new CrudRouteMiddleware($container->reveal());
        $middleware->process($request->reveal(), $handler->reveal());
    }

    public function testProcessWithoutForm()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $routeResult = $this->prophesize(RouteResult::class);
        $route = $this->prophesize(Route::class);
        $container = $this->prophesize(ContainerInterface::class);
        $options = ['foo' => uniqid('something')];
        $handler = $this->prophesize(RequestHandlerInterface::class);

        $request->getAttribute(RouteResult::class)->willReturn($routeResult);
        $routeResult->getMatchedRoute()->willReturn($route);
        $route->getOptions()->willReturn($options);

        $request->withAttribute(
            CrudRouteMiddleware::CRUD_CONFIG,
            $options
        )->shouldBeCalled()
            ->willReturn($request);

        $handler->handle($request->reveal());

        $middleware = new CrudRouteMiddleware($container->reveal());
        $middleware->process($request->reveal(), $handler->reveal());
    }
}