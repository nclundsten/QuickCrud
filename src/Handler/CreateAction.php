<?php

namespace Crud\Handler;

use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface;

class CreateAction extends AbstractCrudWriteHandler
{
    protected $templateName = 'crud::create';

    /**
     * @param Request $request
     * @return HtmlResponse
     */
    protected function handleGet(Request $request) : HtmlResponse
    {
        $this->form->bind(new $this->entityName);
        static::decorateFormWithCsrf(
            $this->form,
            static::generateCsrfToken($request)
        );
        return new HtmlResponse($this->templateRenderer->render($this->templateName, ['form' => $this->form]));
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    protected function handlePost(Request $request) : ResponseInterface
    {
        if (! static::validateCsrfToken($request)) {
            return new EmptyResponse();
        }

        $this->form->bind(new $this->entityName);
        static::decorateFormWithCsrf(
            $this->form,
            static::generateCsrfToken($request)
        );
        $this->form->setData($request->getParsedBody());
        if (! $this->form->isValid()) {
            return new HtmlResponse($this->templateRenderer->render($this->templateName, ['form' => $this->form]));
        }
        $this->entityManager->persist($this->form->getData());
        $this->entityManager->flush();
        return new RedirectResponse($this->router->generateUri($this->routePrefix . '.list'));
    }
}