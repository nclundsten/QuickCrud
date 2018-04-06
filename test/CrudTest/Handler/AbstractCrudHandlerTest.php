<?php

namespace CrudTest\Handler;

use Crud\Middleware\CrudRouteMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use CrudTest\Resource\ConcreteCrudHandler;

class AbstractCrudHandlerTest extends TestCase
{
    protected $config;
    protected $mockRouter;
    protected $mockRequest;
    protected $mockTemplateRenderer;
    protected $mockEntityManager;
    protected $requestAttributes = [];
    protected $requestMethod;

    public function setUp()
    {
        $this->mockRouter = $this->prophesize(RouterInterface::class);
        $this->mockRequest = $this->prophesize(ServerRequestInterface::class);
        $this->mockTemplateRenderer = $this->prophesize(TemplateRendererInterface::class);
        $this->mockEntityManager = $this->prophesize(EntityManagerInterface::class);

        $this->config = [
            'entityName' => uniqid('EntityName'),
            'templateName' => uniqid('some::template'),
            'routes' => ['success' => uniqid('some.route')],
            'identifier' => ['id1' => 'id1', 'id2' => 'id2'],
        ];
        $this->requestAttributes[CrudRouteMiddleware::CRUD_CONFIG] = $this->config;
        $this->mockRequest->getMethod()->willReturn('GET');
        $this->mockRequest->getAttribute(CrudRouteMiddleware::CRUD_CONFIG)->willReturn($this->config);
        $this->mockRequest->getAttributes()->willReturn($this->requestAttributes);
    }

    /**
     * @throws \ReflectionException
     */
    public function testInit()
    {
        $handler = new ConcreteCrudHandler(
            $this->mockTemplateRenderer->reveal(),
            $this->mockRouter->reveal(),
            $this->mockEntityManager->reveal()
        );
        $handler->handle($this->mockRequest->reveal());

        $handlerProps = $handler->getInitProperties();

        $this->assertSame(
            $this->config['entityName'],
            $handlerProps['entityName']
        );
        $this->assertSame(
            $this->config['routes'],
            $handlerProps['routes']
        );
        $this->assertSame(
            $this->config['templateName'],
            $handlerProps['templateName']
        );
    }

    /**
     * @throws \Exception
     */
    public function testIdFromReqeustNoIdThrowsException()
    {
        $handler = new ConcreteCrudHandler(
            $this->mockTemplateRenderer->reveal(),
            $this->mockRouter->reveal(),
            $this->mockEntityManager->reveal()
        );
        $handler->handle($this->mockRequest->reveal());

        self::expectException(\Exception::class, 'expected identifier as array');

        $handler->idFromRequestPublic();
    }

    /**
     * @throws \Exception
     */
    public function testIdFromReqeust()
    {
        $one = random_int(1,100);
        $two = random_int(1,100);
        $this->mockRequest->getAttribute('id1')->willReturn($one);
        $this->mockRequest->getAttribute('id2')->willReturn($two);

        $handler = new ConcreteCrudHandler(
            $this->mockTemplateRenderer->reveal(),
            $this->mockRouter->reveal(),
            $this->mockEntityManager->reveal()
        );
        $handler->handle($this->mockRequest->reveal());

        $idFromRequest = $handler->idFromRequestPublic();
        $this->assertSame(
            [
                'id1' => $one,
                'id2' => $two,
            ],
            $idFromRequest
        );
    }

    /**
     * @throws \Exception
     */
    public function testFindEntityFromRequestNotFoundThrowsException()
    {
        $one = random_int(1,100);
        $two = random_int(1,100);
        $this->mockRequest->getAttribute('id1')->willReturn($one);
        $this->mockRequest->getAttribute('id2')->willReturn($two);

        $this->mockEntityManager->find(
            $this->config['entityName'],
            ['id1' => $one, 'id2' => $two]
        )->willReturn(false);

        $handler = new ConcreteCrudHandler(
            $this->mockTemplateRenderer->reveal(),
            $this->mockRouter->reveal(),
            $this->mockEntityManager->reveal()
        );
        self::expectException(\Exception::class, 'entity not found');

        $handler->handle($this->mockRequest->reveal());

        $handler->findEntityFromRequestPublic();
    }

    /**
     * @throws \Exception
     */
    public function testFindEntityFromRequest()
    {
        $one = random_int(1,100);
        $two = random_int(1,100);
        $this->mockRequest->getAttribute('id1')->willReturn($one);
        $this->mockRequest->getAttribute('id2')->willReturn($two);

        $entity = new \StdClass();
        $this->mockEntityManager->find(
            $this->config['entityName'],
            ['id1' => $one, 'id2' => $two]
        )->willReturn($entity);

        $handler = new ConcreteCrudHandler(
            $this->mockTemplateRenderer->reveal(),
            $this->mockRouter->reveal(),
            $this->mockEntityManager->reveal()
        );

        $handler->handle($this->mockRequest->reveal());

        $this->assertSame($entity, $handler->findEntityFromRequestPublic());
    }
}