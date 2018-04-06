<?php

namespace Crud\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Crud\Middleware\CrudRouteMiddleware;
use Zend\Expressive\Csrf\CsrfMiddleware;
use Zend\Form\FormInterface;
use Zend\Form\Element;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractCrudWriteHandler extends AbstractCrudHandler
{
    /* @var FormInterface */
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

    abstract protected function handlePost() : ResponseInterface;

    protected function init(ServerRequestInterface $request)
    {
        parent::init($request);
        $config = $request->getAttribute(CrudRouteMiddleware::CRUD_CONFIG);
        $this->form = $config['form'];
    }

    /**
     * @param null $entity
     * @param array|null $data
     * @param string $action
     * @param string $method
     * @return FormInterface
     * @throws \Exception
     */
    protected function getForm(
        $entity = null,
        array $data = null,
        string $action='',
        string $method="POST"
    ) : FormInterface {
        $form = $this->form;
        if (null === $entity) {
            $entity = self::findEntityFromRequest();
        }

        $form->bind($entity);

        $form->setAttribute('action', $action);
        $form->setAttribute('method', $method);

        //CSRF Element
        $form->add($csrf = new Element\Hidden('__csrf'));
        $csrf->setValue(
            $this->request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE)
                ->generateToken()
        );

        //Submit
        $form->add($submit = new Element\Submit('submit'));
        $submit->setValue('Submit');

        if (is_array($data)) {
            $form->setData($data);
        }

        return $form;
    }

    /**
     * @return bool
     */
    protected function validateCsrfToken()
    {
        $guard = $this->request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $data = $this->request->getParsedBody();
        $token = $data['__csrf'] ?? '';
        if (!$guard->validateToken($token)) {
            return false;
        }
        return true;
    }
}