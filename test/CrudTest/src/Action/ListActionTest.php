<?php

namespace CrudTest\Action;

use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\ORM\EntityManagerInterface;
use Zend\Expressive\Router\RouterInterface;

use PHPUnit\Framework\TestCase;
use Crud\Handler\ListAction;
use CrudTest\Resource\TestDoctrineEntity;


class ListActionTest extends TestCase
{
    /* @var  MockObject | TemplateRendererInterface*/
    private $templateRenderer;
    /* @var MockObject | RouterInterface */
    private $router;
    private $entityName;
    /* @var MockObject | EntityManagerInterface */
    private $entityManager;
    private $routePrefix;
    private $templateName;

    /* @var MockObject | Request */
    private $request;
    /* @var MockObject | Response */
    private $response;
    /* @var callable */
    private $callable;

    /* @var MockObject | EntityRepository */
    private $repository;
    /* @var ListAction */
    private $listAction;

    private $requestQueryParams = [];

    public function setUp()
    {
        //construct params
        $this->templateRenderer = self::getMockBuilder(TemplateRendererInterface::class)->getMock();
        $this->router = self::getMockBuilder(RouterInterface::class)->getMock();
        $this->router->method('generateUri')
            ->willReturnCallback(function($routeName, $params) {
                if ($routeName === "$this->routePrefix.update") {
                    $id = $params['id'];
                    return "$this->routePrefix/$id/update";
                }
                if ($routeName === "$this->routePrefix.update") {
                    $id = $params['id'];
                    return "$this->routePrefix/$id/update";
                }
                if ($routeName === "$this->routePrefix.create") {
                    return "$this->routePrefix/create";
                }
                if ($routeName === "$this->routePrefix.delete") {
                    $id = $params['id'];
                    return "$this->routePrefix/$id/delete";
                }
                return '';
            });
        $this->entityName = TestDoctrineEntity::class;
        $this->entityManager = self::getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->entityManager->method('getRepository')
            ->willReturnCallback(function($entityName) {
                return $this->repository;
            });
        $this->routePrefix = 'testrouteprefix';
        $this->templateName = 'test::list';

        //invoke params
        $this->request = self::getMockBuilder(Request::class)->getMock();
        $this->request->method('getQueryParams')
            ->willReturnCallback(function () {
                return $this->requestQueryParams;
            });
        $this->response = self::getMockBuilder(Response::class)->getMock();
        $this->callable = function () { };

        $this->repository = self::getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listAction = new ListAction(
            $this->templateRenderer,
            $this->router,
            $this->entityName,
            $this->entityManager,
            $this->routePrefix,
            $this->templateName
        );

    }

    public function testGetReturnsHtmlResponse()
    {
        $this->requestQueryParams['limit'] = '33';
        $this->requestQueryParams['page'] = '99';
        $this->requestQueryParams['offset'] = '66';
        $this->requestQueryParams['order'] = 'id';
        $this->requestQueryParams['direction'] = 'DESC';

        $this->request->method('getMethod')
            ->willReturn('GET');

        $this->repository->expects(self::once())
            ->method('findBy')
            ->with(
                [],
                [
                    $this->requestQueryParams['order'] => $this->requestQueryParams['direction']
                ],
                $this->requestQueryParams['limit'],
                $this->requestQueryParams['offset']
            );

        $this->templateRenderer//->expects(self::once())
            ->method('render')
            ->with(
                $this->templateName
            )->willReturnCallback(function ($template, $data) {
                    self::assertArrayHasKey('entities', $data);
                    self::assertArrayHasKey('editRoute', $data);
                    self::assertSame("$this->routePrefix.update", $data['editRoute']);
                    self::assertArrayHasKey('newUrl', $data);
                    self::assertSame("$this->routePrefix/create", $data['newUrl']);
                    self::assertArrayHasKey('deleteRoute', $data);
                    self::assertSame("$this->routePrefix.delete", $data['deleteRoute']);
                    self::assertArrayHasKey('limit', $data);
                    self::assertSame($this->requestQueryParams['limit'], $data['limit']);
                    self::assertArrayHasKey('page', $data);
                    self::assertSame($this->requestQueryParams['page'], $data['page']);
                    self::assertArrayHasKey('offset', $data);
                    self::assertSame($this->requestQueryParams['offset'], $data['offset']);
                    self::assertArrayHasKey('order', $data);
                    self::assertSame($this->requestQueryParams['order'], $data['order']);
                    self::assertArrayHasKey('direction', $data);
                    self::assertSame($this->requestQueryParams['direction'], $data['direction']);
                    return 'renderedTemplate';
            });


        $response = $this->listAction->__invoke($this->request, $this->response, $this->callable);

        self::assertInstanceOf(HtmlResponse::class, $response);
    }

}