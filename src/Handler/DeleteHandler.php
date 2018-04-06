<?php

namespace Crud\Handler;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\EmptyResponse;

class DeleteHandler extends AbstractCrudWriteHandler
{
    protected $templateName = "crud::delete";

    /**
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function handleGet() : ResponseInterface
    {
        return new HtmlResponse(
            $this->templateRenderer->render(
                $this->templateName,
                [
                    'form' => $this->getForm(),
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

        $form = $this->getForm();
        if ($form->isValid()) {
            $this->entityManager->remove($form->getData());
            $this->entityManager->flush();
            return new RedirectResponse($this->router->generateUri($this->routes['success']));
        }

        return new HtmlResponse(
            $this->templateRenderer->render(
                $this->templateName,
                [
                    'form' => $form,
                ]
            )
        );
    }
}
