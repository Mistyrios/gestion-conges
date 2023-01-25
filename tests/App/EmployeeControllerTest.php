<?php

namespace App;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeControllerTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function should_list_employee(): void
    {
        $repository = $this->createMock(EmployeeRepository::class);
        $repository->method('list')->willReturn(new ArrayCollection([
            new Employee('626e9b71-54f6-44fd-9539-0120cf37daf6',
                'John',
                'Doe',
                8,
                1),
            new Employee('35c5382b-04c8-4555-aa5c-d07631ef19b5',
                'Jane',
                'Doe',
                8,
                1),
        ]));
        $controller = new EmployeeController($repository);

        $response = $controller->list();

        self::assertThat($response, self::isInstanceOf(JsonResponse::class));
        self::assertThat($response->getContent(), self::equalTo('[{"id":"626e9b71-54f6-44fd-9539-0120cf37daf6","firstName":"John","lastName":"Doe","vacationDays":8,"compensatoryTimeDays":1},{"id":"35c5382b-04c8-4555-aa5c-d07631ef19b5","firstName":"Jane","lastName":"Doe","vacationDays":8,"compensatoryTimeDays":1}]'));
    }

    /**
     * @test
     */
    public function should_get_employee_from_id(): void
    {
        $repository = $this->createMock(EmployeeRepository::class);
        $repository->method('get')->willReturn(
            new Employee('626e9b71-54f6-44fd-9539-0120cf37daf6', 'John', 'Doe', 8, 1)
        );
        $controller = new EmployeeController($repository);

        $response = $controller->get(1);

        self::assertThat($response, self::isInstanceOf(JsonResponse::class));
        self::assertThat($response->getContent(), self::equalTo('{"id":"626e9b71-54f6-44fd-9539-0120cf37daf6","firstName":"John","lastName":"Doe","vacationDays":8,"compensatoryTimeDays":1}'));
    }

    /**
     * @test
     */
    public function should_return_HTTP_status_201_add_employee()
    {
        $repository = $this->createMock(EmployeeRepository::class);
        $repository->method('add');
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"firstName":"John","lastName":"Doe","vacationDays":8,"compensatoryTimeDays":1}');

        $controller = new EmployeeController($repository);

        $response = $controller->add($request);

        self::assertThat($response->getStatusCode(), self::equalTo(Response::HTTP_CREATED));
    }

    /**
     * @test
     */
    public function should_return_HTTP_status_200_update_employee(): void
    {
        $repository = $this->createMock(EmployeeRepository::class);
        $repository->method('update');
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"firstName":"Romain","lastName":"Allemand","vacationDays":9,"compensatoryTimeDays":2}');
        $id = "626e9b71-54f6-44fd-9539-0120cf37daf6";
        $controller = new EmployeeController($repository);

        $response = $controller->update($id, $request);

        self::assertThat($response->getStatusCode(), self::equalTo(Response::HTTP_OK));
    }
    /**
     * @test
     */
    public function should_return_HTTP_status_500__when_add_employee_failed()
    {
        $repository = $this->createMock(EmployeeRepository::class);
        $repository->method('add')->willThrowException(new Exception());
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"firstName":"John","lastName":"Doe","vacationDays":8,"compensatoryTimeDays":1}');

        $controller = new EmployeeController($repository);

        $response = $controller->add($request);

        self::assertThat($response->getStatusCode(), self::equalTo(Response::HTTP_INTERNAL_SERVER_ERROR));
    }

    /**
     * @test
     */
    public function should_delete_employee()
    {
        $repository = $this->createMock(EmployeeRepository::class);
        $repository->method('delete')->willReturn(1);

        $controller = new EmployeeController($repository);

        $response = $controller->delete('626e9b71-54f6-44fd-9539-0120cf37daf6');

        self::assertThat($response->getStatusCode(), self::equalTo(Response::HTTP_OK));
    }
}
