<?php

namespace App;


use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RouterTest extends TestCase
{
    /**
     * @test
     */
    public function should_call_controller_method_from_url()
    {
        $controller = $this->createMock(EmployeeController::class);
        $router = new Router();
        $router->add('/^\/$/', $controller, 'list');
        $controller->expects(self::once())
            ->method('list')
            ->with(self::isInstanceOf(Request::class));

        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')
            ->willReturn('/');

        $response = $router->execute($request);

        self::assertThat($response, self::isInstanceOf(Response::class));
    }

    /**
     * @test
     */
    public function should_call_controller_method_and_pass_url_argument()
    {
        $controller = $this->createMock(EmployeeController::class);
        $router = new Router();
        $router->add('/^\/employee\/([a-zA-Z0-9-]+)$/', $controller, 'get');
        $controller->expects(self::once())
            ->method('get')
            ->with(
                self::equalTo('626e9b71-54f6-44fd-9539-0120cf37daf6'),
                self::isInstanceOf(Request::class)
            );

        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')
            ->willReturn('/employee/626e9b71-54f6-44fd-9539-0120cf37daf6');

        $response = $router->execute($request);

        self::assertThat($response, self::isInstanceOf(Response::class));
    }

    /**
     * @test
     */
    public function should_call_controller_method_and_pass_request()
    {
        $controller = $this->createMock(EmployeeController::class);
        $router = new Router();
        $router->add('/^\/employee\/save$/', $controller, 'add');
        $controller->expects(self::once())
            ->method('add')
            ->with(
                self::isInstanceOf(Request::class)
            );

        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')
            ->willReturn('/employee/save');
        $request->method('getContent')
            ->willReturn('{
                "id":"626e9b71-54f6-44fd-9539-0120cf37daf6",
                "title":"id",
                "text":"title",
                "date":"text",
                "author":"date",
                "url":"author"
            }');

        $response = $router->execute($request);

        self::assertThat($response, self::isInstanceOf(Response::class));
    }

    /**
     * @test
     */
    public function should_return_HTTP_NOT_FOUND_Response_when_no_route_match()
    {
        $router = new Router();

        $request = $this->createMock(Request::class);

        $response = $router->execute($request);

        self::assertThat($response, self::isInstanceOf(Response::class));
        self::assertThat($response->getStatusCode(), self::equalTo(Response::HTTP_NOT_FOUND));
    }
}
