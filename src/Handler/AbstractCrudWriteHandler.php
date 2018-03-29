<?php

namespace Crud\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Crud\Middleware\CrudRouteMiddleware;
use Zend\Expressive\Csrf\CsrfMiddleware;
use Zend\Form\FormInterface;
use Zend\Form\Element;

class AbstractCrudWriteHandler extends AbstractCrudHandler
{
    protected $form;

    protected function init(ServerRequestInterface $request)
    {
        parent::init($request);
        $config = $request->getAttribute(CrudRouteMiddleware::CRUD_CONFIG);
        $this->form = $config['form'];
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

    public static function validateCsrfToken(ServerRequestInterface $request)
    {
        $guard = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $data = $request->getParsedBody();
        $token = $data['__csrf'] ?? '';
        if (!$guard->validateToken($token)) {
            return false;
        }
        return true;
    }

    public static function generateCsrfToken(ServerRequestInterface $request)
    {
        return $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE)->generateToken();
    }
}