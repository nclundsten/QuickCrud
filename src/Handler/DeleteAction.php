<?php

namespace Crud\Handler;

use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\EmptyResponse;

class DeleteAction extends AbstractCrudWriteHandler
{
    protected $templateName = "crud::delete";

    protected function handleGet(Request $request)
    {
        $entity = $this->entityManager->find($this->entityName, self::idFromRequest($request));
        $this->form->bind($entity);
        self::decorateFormWithCsrf(
            $this->form,
            self::generateCsrfToken($request)
        );
        $vars = [
            'form' => $this->form,
            'entity' => $entity,
        ];
        return new HtmlResponse($this->templateRenderer->render($this->templateName, $vars));
    }

    protected function handlePost(Request $request)
    {
        if (! self::validateCsrfToken($request)) {
            return new EmptyResponse();
        }

        $entity = $this->entityManager->find($this->entityName, self::idFromRequest($request));
        $this->form->bind($entity);
        if (! $this->form->isValid()) {
            self::decorateFormWithCsrf(
                $this->form,
                self::generateCsrfToken($request)
            );
            $vars = [
                'form' => $this->form,
                'entity' => $entity,
            ];
            return new HtmlResponse($this->templateRenderer->render($this->templateName, $vars));
        }
        $this->entityManager->remove($this->form->getData());
        $this->entityManager->flush();
        return new RedirectResponse($this->router->generateUri($this->routePrefix . '.list'));
    }
}
