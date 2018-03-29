<?php

namespace Crud\Handler;

use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class ListAction extends AbstractCrudHandler
{
    protected $templateName = "crud::list";

    public function handleGet(Request $request) : HtmlResponse
    {
        $params = $request->getQueryParams();
        $limit = isset($params['limit']) ? $params['limit'] : 10;
        $page = isset($params['page']) ? $params['page'] : 1;
        $offset = isset($params['offset']) ? $params['offset'] : $limit * $page - $limit;
        $direction = isset($params['direction']) ? $params['direction'] : 'ASC';
        $order = isset($params['order']) ? $params['order'] : 'id';


        $entities = $this->entityManager->getRepository($this->entityName)
            ->findBy([],[$order => $direction], $limit, $offset);

        $data = [
            'entities' => $entities,
            'editRoute' => $this->routePrefix . '.update',
            'newUrl' => $this->router->generateUri($this->routePrefix . '.create'),
            'deleteRoute' => $this->routePrefix . '.delete',
            'limit' => $limit,
            'page' => $page,
            'offset' => $offset,
            'order' => $order,
            'direction' => $direction
        ];

        return new HtmlResponse($this->templateRenderer->render($this->templateName, $data));
    }
}