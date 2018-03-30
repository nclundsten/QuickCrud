<?php

namespace Crud\Handler;

use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;

class CreateAction extends AbstractCrudWriteHandler
{
    protected $templateName = 'crud::create';

    /**
     * @return HtmlResponse
     * @throws \Exception
     */
    protected function handleGet() : HtmlResponse
    {
        $form = self::getForm(new $this->entityName);
        return new HtmlResponse($this->templateRenderer->render($this->templateName, ['form' => $form]));
    }

    /**
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function handlePost() : ResponseInterface
    {
        if (! static::validateCsrfToken()) {
            return new EmptyResponse();
        }

        $form = self::getForm(new $this->entityName);
        $form->setData($this->request->getParsedBody());
        if (! $form->isValid()) {
            return new HtmlResponse($this->templateRenderer->render($this->templateName, ['form' => $form]));
        }
        $this->entityManager->persist($form->getData());
        $this->entityManager->flush();
        return new RedirectResponse($this->router->generateUri($this->routePrefix . '.list'));
    }
}