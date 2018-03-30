<?php

namespace Crud\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Crud\Middleware\CrudRouteMiddleware;
use Zend\Expressive\Csrf\CsrfMiddleware;
use Zend\Form\FormInterface;
use Zend\Form\Element;
use Psr\Http\Message\ResponseInterface;

class AbstractCrudWriteHandler extends AbstractCrudHandler
{
    private $form;

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $this->init($request);
            return $this->handlePost();
        }
        return parent::handle($request);
    }

    protected function handlePost(){}

    protected function init(ServerRequestInterface $request)
    {
        parent::init($request);
        $config = $request->getAttribute(CrudRouteMiddleware::CRUD_CONFIG);
        $this->form = $config['form'];
    }

    /**
     * @param object|null $entity
     * @param string $action
     * @param string $method
     * @return mixed
     * @throws \Exception
     */
    protected function getForm($entity = null, $action='', $method="POST")
    {
        $form = $this->form;
        if (null === $entity) {
            $entity = self::findEntityFromRequest();
        }

        $form->bind($entity);
        static::decorateFormWithCsrf($form, self::generateCsrfToken(), $action, $method);

        return $form;
    }

    public static function decorateFormWithCsrf(FormInterface $form, $csrfToken, $action='', $method="POST")
    {
        $form->setAttribute('method', $method);
        $form->setAttribute('action', $action);

        //CSRF Element
        $form->add($csrf = new Element\Hidden('__csrf'));
        $csrf->setValue($csrfToken);

        //Submit
        $form->add($submit = new Element\Submit('submit'));
        $submit->setValue('Submit');
    }

    /**
     * @return bool
     */
    public function validateCsrfToken()
    {
        $guard = $this->request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $data = $this->request->getParsedBody();
        $token = $data['__csrf'] ?? '';
        if (!$guard->validateToken($token)) {
            return false;
        }
        return true;
    }

    public function generateCsrfToken()
    {
        return $this->request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE)->generateToken();
    }
}