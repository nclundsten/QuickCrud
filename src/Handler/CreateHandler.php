<?php

namespace Crud\Handler;

use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;

class CreateHandler extends AbstractCrudWriteHandler
{
    protected $templateName = 'crud::create';

    /**
     * @return HtmlResponse
     * @throws \Exception
     */
    protected function handleGet() : ResponseInterface
    {
        return new HtmlResponse(
            $this->templateRenderer->render(
                $this->templateName,
                [
                    'form' => $this->getForm(new $this->entityName),
                ]
            )
        );
    }

    /**
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function handlePost() : ResponseInterface
    {
        if (! $this->validateCsrfToken()) {
            return new EmptyResponse();
        }

        $form = $this->getForm(new $this->entityName, $this->request->getParsedBody());
        if ($form->isValid()) {
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();
            return new RedirectResponse($this->router->generateUri($this->routePrefix . '.list'));
        }

        return new HtmlResponse(
            $this->templateRenderer->render(
                $this->templateName,
                ['form' => $form]
            )
        );
    }
}