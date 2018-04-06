<?php

namespace CrudTest\Handler;

use Crud\Handler\CreateHandler;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Crud\Middleware\CrudRouteMiddleware;
use Zend\Form\FormInterface;

class CreateHandlerTest extends TestCase
{
    private $mockRouter;
    private $mockTemplateRenderer;
    private $mockEntityManager;

    /** @var ServerRequestInterface | ObjectProphecy */
    private $mockRequest;

    private $mockForm;

    private $requestAttributes;
    private $config;

    public function setUp()
    {
        $this->mockRouter = $this->prophesize(RouterInterface::class);
        $this->mockTemplateRenderer = $this->prophesize(TemplateRendererInterface::class);
        $this->mockEntityManager = $this->prophesize(EntityManagerInterface::class);

        $this->mockRequest = $this->prophesize(ServerRequestInterface::class);

        $this->mockForm = $this->prophesize(FormInterface::class);
        $this->config = [
            'entityName' => "\\StdClass",
            'routePrefix' => uniqid('routeprefix'),
            'templateName' => uniqid('some::template'),
            'form' => $this->mockForm,
        ];
        $this->requestAttributes[CrudRouteMiddleware::CRUD_CONFIG] = $this->config;
        $this->mockRequest->getMethod()->willReturn('POST');
        $this->mockRequest->getAttribute(CrudRouteMiddleware::CRUD_CONFIG)->willReturn($this->config);
        $this->mockRequest->getAttributes()->willReturn($this->requestAttributes);
    }

    private function getHandler()
    {
        $handler = self::getMockBuilder(CreateHandler::class)
            ->setConstructorArgs([
                $this->mockTemplateRenderer->reveal(),
                $this->mockRouter->reveal(),
                $this->mockEntityManager->reveal(),
            ])->setMethods(['getForm', 'validateCsrfToken'])
            ->getMock();
        $handler->method('getForm')->willReturn($this->mockForm->reveal());

        return $handler;
    }

    public function testHandleGet()
    {
        $this->mockRequest->getMethod()->willReturn('GET');

        $this->mockTemplateRenderer->render(
            $this->config['templateName'],
            ['form' => $this->mockForm]
        )->shouldBeCalled();

        $this->assertInstanceOf(
            HtmlResponse::class,
            $this->getHandler()->handle($this->mockRequest->reveal())
        );

    }

    public function testHandlePostInvalidCsrf()
    {
        $this->mockRequest->getMethod()->willReturn('POST');

        $handler = $this->getHandler();
        $handler->method('validateCsrfToken')->willReturn(false);

        $this->assertInstanceOf(
            EmptyResponse::class,
            $handler->handle($this->mockRequest->reveal())
        );
    }

    public function testHandlePost()
    {
        $this->mockRequest->getMethod()->willReturn('POST');
        $this->mockRequest->getParsedBody()->willReturn([]);

        $handler = $this->getHandler();
        $handler->method('validateCsrfToken')->willReturn(true);

        $this->mockTemplateRenderer->render(
            $this->config['templateName'],
            ['form' => $this->mockForm->reveal()]
        )->shouldBeCalled();

        $this->assertInstanceOf(
            HtmlResponse::class,
            $handler->handle($this->mockRequest->reveal())
        );
    }

    public function testHandlePostValidFormPersists()
    {
        $this->mockRequest->getMethod()->willReturn('POST');
        $this->mockRequest->getParsedBody()->willReturn([]);
        $this->mockRouter->generateUri($this->config['routePrefix'] . '.list')->willReturn(uniqid('uri'));

        $this->mockForm->isValid()->willReturn(true)->shouldBeCalled();
        $entity = new \StdClass();
        $this->mockForm->getData()->willReturn($entity)->shouldBeCalled();
        $this->mockEntityManager->persist($entity)->shouldBeCalled();
        $this->mockEntityManager->flush()->shouldBeCalled();

        $handler = $this->getHandler();
        $handler->method('validateCsrfToken')->willReturn(true);

        $this->assertInstanceOf(
            RedirectResponse::class,
            $handler->handle($this->mockRequest->reveal())
        );
    }
}