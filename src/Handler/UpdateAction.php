<?php

namespace Crud\Handler;

use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\EmptyResponse;

class UpdateAction extends AbstractCrudWriteHandler
{
    protected $templateName = "crud::update";


    protected function handleGet(Request $request)
    {
        $this->form->bind($this->entityManager->find($this->entityName, self::idFromRequest($request)));
        static::decorateFormWithCsrf(
            $this->form,
            static::generateCsrfToken($request)
        );
        return new HtmlResponse($this->templateRenderer->render($this->templateName, ['form' => $this->form]));
    }

    protected function handlePost(Request $request)
    {
        if (! static::validateCsrfToken($request)) {
            return new EmptyResponse();
        }

        $this->form->bind($this->entityManager->find($this->entityName, self::idFromRequest($request)));
        $this->form->setData($request->getParsedBody());
        if (! $this->form->isValid()) {
            static::decorateFormWithCsrf(
                $this->form,
                static::generateCsrfToken($request)
            );
            return new HtmlResponse($this->templateRenderer->render($this->templateName, ['form' => $this->form]));
        }
        $this->entityManager->persist($this->form->getData());
        $this->entityManager->flush();
        return new RedirectResponse($this->router->generateUri($this->routePrefix . '.list'));
    }
}