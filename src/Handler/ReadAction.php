<?php

namespace Crud\Handler;

use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadAction extends AbstractCrudHandler
{
    protected $templateName = "crud::read";

    public function handleGet(Request $request)
    {
        $entity = $this->entityManager->find($this->entityName, self::idFromRequest($request));

        return new HtmlResponse($this->templateRenderer->render($this->templateName, ['entity' => $entity]));
    }
}

