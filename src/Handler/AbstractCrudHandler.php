<?php

namespace Crud\Handler;

use Crud\Middleware\CrudRouteMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractCrudHandler
{
    protected $templateRenderer;

    protected $router;

    protected $entityName;

    protected $entityManager;

    protected $routePrefix;

    protected $templateName;

    protected $identifier;

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
    public function __invoke(ServerRequestInterface $request) : ResponseInterface
    {
        $this->init($request);

        if ($request->getMethod() === 'POST') {
            return $this->handlePost($request);
        }

        return $this->handleGet($request);
    }

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
     * @param ServerRequestInterface $request
     * @return array
     * @throws \Exception
     */
    protected function idFromRequest(ServerRequestInterface $request) : array
    {
        $identifier = $this->identifier;
        if (! is_array($identifier)) {
            throw new \Exception('expected identifier as array');
        }
        foreach ($identifier as $key => $requestAttribute) {
            $identifier[$key] = $request->getAttribute($requestAttribute);
        }
        return $identifier;
    }
}