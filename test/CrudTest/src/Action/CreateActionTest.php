<?php

namespace CrudTest\Action;

use PHPUnit\Framework\MockObject\MockObject;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Expressive\Csrf\CsrfGuardInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Form\Form;
use Zend\Form\FormInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\ORM\EntityManagerInterface;
use Zend\Expressive\Router\RouterInterface;

use PHPUnit\Framework\TestCase;
use Crud\Handler\CreateAction;
use CrudTest\Resource\TestDoctrineEntity;


class CreateActionTest extends TestCase
{
    /* @var  MockObject | TemplateRendererInterface*/
    private $templateRenderer;
    /* @var MockObject | RouterInterface */
    private $router;
    private $entityName;
    /* @var MockObject | EntityManagerInterface */
    private $entityManager;
    /* @var MockObject | Form */
    private $form;
    private $routePrefix;
    private $templateName;

    /* @var MockObject | Request */
    private $request;
    /* @var MockObject | Response */
    private $response;
    /* @var callable */
    private $callable;

    /* @var CreateAction */
    private $createAction;

    private $requestAttributes = [];

    public function setUp()
    {
        //construct params
        $this->templateRenderer = self::getMockBuilder(TemplateRendererInterface::class)->getMock();
        $this->router = self::getMockBuilder(RouterInterface::class)->getMock();
        $this->router->method('generateUri')
            ->willReturnCallback(function($routeName) {
                if ($routeName === "$this->routePrefix.create") {
                    return "$this->routePrefix/create";
                }
                if ($routeName === "$this->routePrefix.list") {
                    return "$this->routePrefix/list";
                }
                return '';
            });
        $this->entityName = TestDoctrineEntity::class;
        $this->entityManager = self::getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->form = self::getMockBuilder(Form::class)
            ->setMethodsExcept(['add', 'get', 'has', 'getAttribute', 'setAttribute'])
            ->getMock();
        $this->routePrefix = 'testrouteprefix';
        $this->templateName = 'test::create';

        //invoke params
        $this->request = self::getMockBuilder(Request::class)->getMock();
        $this->request->method('getAttribute')
            ->willReturnCallback(function($attributeName){
                return $this->requestAttributes[$attributeName];
            });
        $this->response = self::getMockBuilder(Response::class)->getMock();
        $this->callable = function () { };

        $this->requestAttributes['csrf'] = self::getMockBuilder(CsrfGuardInterface::class)->getMock();

        $this->createAction = new CreateAction(
            $this->templateRenderer,
            $this->router,
            $this->entityName,
            $this->entityManager,
            $this->form,
            $this->routePrefix,
            $this->templateName
        );

    }

    public function testGetReturnsHtmlResponse()
    {
        $this->request->expects(self::once())
            ->method('getMethod')
            ->willReturn('GET');

        $this->templateRenderer->expects(self::once())
            ->method('render')
            ->with(
                $this->templateName,
                ['form' => $this->form]
            )->willReturn('mockContent');

        $response = $this->createAction->__invoke($this->request, $this->response, $this->callable);

        self::assertInstanceOf(HtmlResponse::class, $response);

        $this->assertFormDecorated();
    }

    public function testPostWithInvalidCsrf()
    {
        $this->request->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->requestAttributes['csrf']->expects(self::once())
            ->method('validateToken')->willReturn(false);

        $response = $this->createAction->__invoke($this->request, $this->response, $this->callable);

        self::assertInstanceOf(EmptyResponse::class, $response);
    }

    public function testPostWithNotValidForm()
    {
        $this->request->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->requestAttributes['csrf']->expects(self::once())
            ->method('validateToken')->willReturn(true);

        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        $this->templateRenderer->expects(self::once())
            ->method('render')
            ->with(
                $this->templateName,
                ['form' => $this->form]
            )->willReturn('mockContent');

        $response = $this->createAction->__invoke($this->request, $this->response, $this->callable);

        $this->assertFormDecorated();

        self::assertInstanceOf(HtmlResponse::class, $response);
    }

    protected function assertFormDecorated()
    {
        //form was decorated with csrf, submit, and method
        self::assertTrue($this->form->has('__csrf'));
        self::assertTrue($this->form->has('submit'));
        self::assertSame('POST', $this->form->getAttribute('method'));
        self::assertSame($this->routePrefix . '/create', $this->form->getAttribute('action'));
    }

    public function testPostWithValidForm()
    {
        $this->request->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->requestAttributes['csrf']->expects(self::once())
            ->method('validateToken')
            ->willReturn(true);

        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $entity = new TestDoctrineEntity();
        $this->form->expects(self::once())
            ->method('getData')
            ->willReturn($entity);
        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with($entity);

        $this->entityManager->expects(self::once())
            ->method('flush');

        $response = $this->createAction->__invoke($this->request, $this->response, $this->callable);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame("$this->routePrefix/list", $response->getHeaderLine('location'));
    }


}