<?php

namespace Crud\Handler;

use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;

class UpdateHandler extends AbstractCrudWriteHandler
{
    protected $templateName = "crud::update";

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
                    'form' => $this->getForm()
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

        $form = $this->getForm(null, $this->request->getParsedBody());
        if ($form->isValid()) {
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();
            return new RedirectResponse($this->router->generateUri($this->routes['success']));
        }

        return new HtmlResponse(
            $this->templateRenderer->render(
                $this->templateName,
                [
                    'form' => $form
                ]
            )
        );
    }
}