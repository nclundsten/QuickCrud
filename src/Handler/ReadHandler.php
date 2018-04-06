<?php

namespace Crud\Handler;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;

class ReadHandler extends AbstractCrudHandler
{
    protected $templateName = "crud::read";

    /**
     * @return HtmlResponse
     * @throws \Exception
     */
    public function handleGet() : ResponseInterface
    {
        return new HtmlResponse(
            $this->templateRenderer->render(
                $this->templateName,
                [
                    'entity' => $this->findEntityFromRequest(),
                ]
            )
        );
    }
}

