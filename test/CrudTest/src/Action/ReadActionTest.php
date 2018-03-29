<?php

namespace CrudTest\Action;

use PHPUnit\Framework\MockObject\MockObject;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\ORM\EntityManagerInterface;
use Zend\Expressive\Router\RouterInterface;

use PHPUnit\Framework\TestCase;
use Crud\Handler\ReadAction;
use CrudTest\Resource\TestDoctrineEntity;


class ReadActionTest extends TestCase
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

    /* @var ReadAction */
    private $readAction;

    private $requestAttributes = [];

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
        $this->routePrefix = 'testrouteprefix';
        $this->templateName = 'test::read';

        //invoke params
        $this->request = self::getMockBuilder(Request::class)->getMock();
        $this->request->method('getAttribute')
            ->willReturnCallback(function($attributeName){
                return $this->requestAttributes[$attributeName];
            });
        $this->response = self::getMockBuilder(Response::class)->getMock();
        $this->callable = function () { };

        $this->readAction = new ReadAction(
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
        $this->requestAttributes['id'] = 99;
        $this->request->method('getMethod')
            ->willReturn('GET');

        $entity = new TestDoctrineEntity();
        $this->entityManager->expects(self::once())
            ->method('find')
            ->with(
                $this->entityName,
                ['id' => $this->requestAttributes['id']]
            )->willReturn($entity);

        $this->templateRenderer//->expects(self::once())
            ->method('render')
            ->with(
                $this->templateName,
                ['entity' => $entity]
            )->willReturn('renderedTemplate');

        $response = $this->readAction->__invoke($this->request, $this->response, $this->callable);

        self::assertInstanceOf(HtmlResponse::class, $response);
    }

}