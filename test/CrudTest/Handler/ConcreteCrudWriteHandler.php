<?php

namespace CrudTest\Resource;

use Psr\Http\Message\ResponseInterface;
use Crud\Handler\AbstractCrudWriteHandler;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Form\FormInterface;

class ConcreteCrudWriteHandler extends AbstractCrudWriteHandler
{

    /**
     * @return ResponseInterface
     * implement the abstract method
     */
    protected function handlePost() : ResponseInterface
    {
        return EmptyResponse::withHeaders(['method' => 'POST']);
    }

    /**
     * @return ResponseInterface
     * implement the abstract method
     */
    protected function handleGet() : ResponseInterface
    {
        return EmptyResponse::withHeaders(['method' => 'GET']);
    }

    /**
     * @return bool
     * proxy the validateCsrfToken method for easy testing
     */
    public function ValidateCsrfTokenPublic()
    {
        return self::validateCsrfToken();
    }

    /**
     * @param null $entity
     * @param array|null $data
     * @param string $action
     * @param string $method
     * @return FormInterface
     * @throws \Exception
     * proxy the getForm method for easy testing
     */
    public function getFormPublic(
        $entity = null,
        array $data = null,
        string $action='',
        string $method="POST"
    ) : FormInterface {
        return self::getForm($entity, $data, $action, $method);
    }

}