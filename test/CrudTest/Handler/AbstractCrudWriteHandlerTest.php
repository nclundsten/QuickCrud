<?php

namespace CrudTest\Handler;

use CrudTest\Resource\TestDoctrineEntity;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Csrf\CsrfGuardInterface;
use Zend\Expressive\Csrf\CsrfMiddleware;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Crud\Middleware\CrudRouteMiddleware;
use CrudTest\Resource\ConcreteCrudWriteHandler;

class AbstractCrudWriteHandlerTest extends TestCase
{
    protected $mockRequest;
    protected $mockTemplateRenderer;
    protected $mockRouter;
    protected $mockEntityManager;
    protected $requestAttributes;
    protected $form;
    protected $mockCsrf;
    protected $config;

    public function setUp()
    {
        $this->mockRequest = $this->prophesize(ServerRequestInterface::class);
        $this->mockTemplateRenderer = $this->prophesize(TemplateRendererInterface::class);
        $this->mockRouter = $this->prophesize(RouterInterface::class);
        $this->mockEntityManager = $this->prophesize(EntityManagerInterface::class);
        $this->form = new \Zend\Form\Form();
        $this->mockCsrf = $this->prophesize(CsrfGuardInterface::class);

        $this->config = [
            'entityName' => uniqid('EntityName'),
            'templateName' => uniqid('some::template'),
            'identifier' => ['id1' => 'id1', 'id2' => 'id2'],
            'form' => $this->form,
        ];
        $this->requestAttributes[CrudRouteMiddleware::CRUD_CONFIG] = $this->config;
        $this->mockRequest->getMethod()->willReturn('POST');
        $this->mockRequest->getAttribute(CrudRouteMiddleware::CRUD_CONFIG)->willReturn($this->config);
        $this->mockRequest->getAttributes()->willReturn($this->requestAttributes);
        $this->mockRequest->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE)->willReturn($this->mockCsrf);
    }

    public function testHandleHandlesPost()
    {
        $handler = new ConcreteCrudWriteHandler(
            $this->mockTemplateRenderer->reveal(),
            $this->mockRouter->reveal(),
            $this->mockEntityManager->reveal()
        );

        $response = $handler->handle($this->mockRequest->reveal());

        $this->assertSame('POST', $response->getHeaderLine('method'));
    }

    public function testHandleCallsParentToHandleGet()
    {
        $this->mockRequest->getMethod()->willReturn('GET');
        $handler = new ConcreteCrudWriteHandler(
            $this->mockTemplateRenderer->reveal(),
            $this->mockRouter->reveal(),
            $this->mockEntityManager->reveal()
        );

        $response = $handler->handle($this->mockRequest->reveal());

        $this->assertSame('GET', $response->getHeaderLine('method'));
    }

    /**
     * @throws \Exception
     */
    public function testGetForm()
    {
        $one = random_int(1,1000);
        $two = random_int(1,1000);
        $this->mockRequest->getAttribute('id1')->willReturn($one);
        $this->mockRequest->getAttribute('id2')->willReturn($two);

        $this->mockCsrf->generateToken()->willReturn($csrfToken = uniqid('csrftoken'));

        $entity = new TestDoctrineEntity();
        $this->mockEntityManager->find(
            $this->config['entityName'],
            ['id1' => $one, 'id2' => $two]
        )->willReturn($entity);

        $handler = new ConcreteCrudWriteHandler(
            $this->mockTemplateRenderer->reveal(),
            $this->mockRouter->reveal(),
            $this->mockEntityManager->reveal()
        );
        $handler->handle($this->mockRequest->reveal());

        $uri = uniqid('some/uri');
        $method = uniqid('SOMEMETHOD');
        $form = $handler->getFormPublic(null, null, $uri, $method);
        $this->assertSame($this->form, $form);

        //form was decorated with csrf, submit, and method
        self::assertTrue($form->has('__csrf'));
        $csrf = $form->get('__csrf');
        self::assertSame($csrfToken, $csrf->getValue());
        self::assertTrue($form->has('submit'));
        self::assertSame($method, $form->getAttribute('method'));
        self::assertSame($uri, $form->getAttribute('action'));
    }


    public function testValidateCsrfToken()
    {
        $this->mockCsrf->validateToken($csrfToken = uniqid('csrftoken'))
            ->willReturn(true);

        $this->mockRequest->getParsedBody()
            ->willreturn(['__csrf' => $csrfToken]);

        $handler = new ConcreteCrudWriteHandler(
            $this->mockTemplateRenderer->reveal(),
            $this->mockRouter->reveal(),
            $this->mockEntityManager->reveal()
        );
        $handler->handle($this->mockRequest->reveal());

        $this->assertTrue($handler->validateCsrfTokenPublic());
    }

    public function testValidateCsrfTokenInvalid()
    {
        $this->mockCsrf->validateToken($csrfToken = uniqid('csrftoken'))
            ->willReturn(false);

        $this->mockRequest->getParsedBody()
            ->willreturn(['__csrf' => $csrfToken]);

        $handler = new ConcreteCrudWriteHandler(
            $this->mockTemplateRenderer->reveal(),
            $this->mockRouter->reveal(),
            $this->mockEntityManager->reveal()
        );
        $handler->handle($this->mockRequest->reveal());

        $this->assertFalse($handler->validateCsrfTokenPublic());
    }

    public function testValidateCsrfTokenWithMissingToken()
    {
        $this->mockCsrf->validateToken('')->willReturn(false);

        $this->mockRequest->getParsedBody()->willreturn([ /* no token */ ]);

        $handler = new ConcreteCrudWriteHandler(
            $this->mockTemplateRenderer->reveal(),
            $this->mockRouter->reveal(),
            $this->mockEntityManager->reveal()
        );
        $handler->handle($this->mockRequest->reveal());

        $this->assertFalse($handler->validateCsrfTokenPublic());

    }

}