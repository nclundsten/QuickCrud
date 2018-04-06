<?php

namespace CrudTest\Handler;

use Crud\Handler\ReadHandler;
use Crud\Handler\UpdateHandler;
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

class ReadHandlerTest extends TestCase
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

        $this->config = [
            'entityName' => "\\StdClass",
            'templateName' => uniqid('some::template'),
        ];
        $this->requestAttributes[CrudRouteMiddleware::CRUD_CONFIG] = $this->config;
        $this->mockRequest->getMethod()->willReturn('POST');
        $this->mockRequest->getAttribute(CrudRouteMiddleware::CRUD_CONFIG)->willReturn($this->config);
        $this->mockRequest->getAttributes()->willReturn($this->requestAttributes);
    }

    private function getHandler()
    {
        $handler = self::getMockBuilder(ReadHandler::class)
            ->setConstructorArgs([
                $this->mockTemplateRenderer->reveal(),
                $this->mockRouter->reveal(),
                $this->mockEntityManager->reveal(),
            ])->setMethods(['findEntityFromRequest'])
            ->getMock();

        return $handler;
    }

    public function testHandleGet()
    {
        $this->mockRequest->getMethod()->willReturn('GET');

        $handler = $this->getHandler();
        $entity = new \StdClass();
        $handler->method('findEntityFromRequest')->willReturn($entity);

        $this->mockTemplateRenderer->render(
            $this->config['templateName'],
            ['entity' => $entity]
        )->shouldBeCalled();

        $this->assertInstanceOf(
            HtmlResponse::class,
            $handler->handle($this->mockRequest->reveal())
        );

    }

}