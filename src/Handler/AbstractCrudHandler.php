<?php

namespace Crud\Handler;

use Crud\Middleware\CrudRouteMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractCrudHandler implements RequestHandlerInterface
{
    /* @var TemplateRendererInterface */
    protected $templateRenderer;

    /* @var RouterInterface */
    protected $router;

    /* @var string */
    protected $entityName;

    /* @var EntityManagerInterface */
    protected $entityManager;

    /* @var string */
    protected $routePrefix;

    /* @var string */
    protected $templateName;

    /* @var array */
    protected $identifier;

    /* @var ServerRequestInterface */
    protected $request;

    public function __construct(
        TemplateRendererInterface $templateRenderer,
        RouterInterface $router,
        EntityManagerInterface $entityManager
    ) {
        $this->templateRenderer = $templateRenderer;
        $this->router = $router;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->init($request);

        if ($request->getMethod() === 'GET') {
            return $this->handleGet();
        }
    }

    protected function handleGet(){}

    /**
     * @param ServerRequestInterface $request
     */
    protected function init(ServerRequestInterface $request)
    {
        $this->request = $request;
        $config = $request->getAttribute(CrudRouteMiddleware::CRUD_CONFIG);

        $this->entityName = $config['entityName'];
        $this->routePrefix = $config['routePrefix'];

        $this->templateName = isset($config['templateName'])
            ? $config['templateName']
            : $this->templateName;

        $this->identifier = isset($config['identifier'])
            ? $config['identifier']
            : null;
    }

    /**
     * @return object
     * @throws \Exception
     */
    protected function findEntityFromRequest()
    {
        $entity = $this->entityManager->find($this->entityName, self::idFromRequest());
        if (! $entity) {
            throw new \Exception('entity not found');
        }
        return $entity;
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     * @throws \Exception
     */
    protected function idFromRequest() : array
    {
        $identifier = $this->identifier;
        if (! is_array($identifier)) {
            throw new \Exception('expected identifier as array');
        }
        foreach ($identifier as $key => $requestAttribute) {
            $identifier[$key] = $this->request->getAttribute($requestAttribute);
        }
        return $identifier;
    }
}