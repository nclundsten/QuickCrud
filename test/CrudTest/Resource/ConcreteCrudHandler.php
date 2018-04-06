<?php

namespace CrudTest\Resource;

use Crud\Handler\AbstractCrudHandler;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmptyResponse;

class ConcreteCrudHandler extends AbstractCrudHandler
{
    /**
     * @return ResponseInterface
     * implement the abstract method
     */
    protected function handleGet() : ResponseInterface
    {
        return new EmptyResponse();
    }

    /**
     * @return array
     * @throws \Exception
     * proxy the idFromReqeust method for easy testing
     */
    public function idFromRequestPublic()
    {
        return self::idFromRequest();
    }

    /**
     * @return array
     * expose the properties that are configured during the init method
     */
    public function getInitProperties()
    {
        return [
            'entityName' => $this->entityName,
            'routes' => $this->routes,
            'templateName' => $this->templateName,
        ];
    }

    /**
     * @return object
     * @throws \Exception
     * proxy the findEntityFromRequest method for easy testing
     */
    public function findEntityFromRequestPublic()
    {
        return self::findEntityFromRequest();
    }
}