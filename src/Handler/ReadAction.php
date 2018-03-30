<?php

namespace Crud\Handler;

use Zend\Diactoros\Response\HtmlResponse;

class ReadAction extends AbstractCrudHandler
{
    protected $templateName = "crud::read";

    /**
     * @return HtmlResponse
     * @throws \Exception
     */
    public function handleGet() : HtmlResponse
    {
        return new HtmlResponse(
            $this->templateRenderer->render(
                $this->templateName,
                [
                    'entity' => self::findEntityFromRequest(),
                ]
            )
        );
    }
}

