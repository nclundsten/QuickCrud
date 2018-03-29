<?php

namespace Crud\Middleware;

use Interop\Container\ContainerInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;

class CrudRouteMiddleware implements MiddlewareInterface
{
    const CRUD_CONFIG = 'crud.config';

    private $container;

    /**
     * CrudRouteMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $options = $request->getAttribute(RouteResult::class)->getMatchedRoute()->getOptions();

        if (array_key_exists('form', $options)) {
            $form = $this->container->get($options['form']);
            $options['form'] = $form;
        }

        return $handler->process($request->withAttribute(self::CRUD_CONFIG, $options));
    }
}