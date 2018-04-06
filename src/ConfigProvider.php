<?php

namespace Crud;

use ContainerInteropDoctrine\EntityManagerFactory;
use Crud\Middleware;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'crud' => [],
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'factories'  => [
                Handler\ListHandler::class => Handler\ListHandlerFactory::class,
                Handler\ReadHandler::class => Handler\ReadHandlerFactory::class,
                Handler\CreateHandler::class => Handler\CreateHandlerFactory::class,
                Handler\UpdateHandler::class => Handler\UpdateHandlerFactory::class,
                Handler\DeleteHandler::class => Handler\DeleteHandlerFactory::class,

                'doctrine.entity_manager.orm_default' => EntityManagerFactory::class,

                Middleware\CrudRouteMiddleware::class => Middleware\CrudRouteMiddlewareFactory::class,
            ],
            'invokables' => [
                Form\DeleteForm::class => Form\DeleteForm::class,
            ]
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @return array
     */
    public function getTemplates()
    {
        return [
            'paths' => [
                'crud'    => [__DIR__ . '/../templates/crud'],
            ],
        ];
    }
}
