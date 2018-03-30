<?php

namespace Crud\Handler;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\EmptyResponse;

class DeleteAction extends AbstractCrudWriteHandler
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
                    'form' => self::getForm(),
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
        if (! self::validateCsrfToken()) {
            return new EmptyResponse();
        }

        $form = self::getForm();
        if ($form->isValid()) {
            $this->entityManager->remove($form->getData());
            $this->entityManager->flush();
            return new RedirectResponse($this->router->generateUri($this->routePrefix . '.list'));
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
