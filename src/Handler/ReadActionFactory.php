<?php

namespace Crud\Handler;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class ReadActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ReadAction(
            $container->get(TemplateRendererInterface::class),
            $container->get(RouterInterface::class),
            $container->get('doctrine.entity_manager.orm_default')
        );
    }
}