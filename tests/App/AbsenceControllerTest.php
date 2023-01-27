<?php

namespace App;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AbsenceControllerTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     * @throws \JsonException
     */
    public function should_list_absence(): void
    {
        $repository = $this->createMock(AbsenceRepository::class);
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $repository->method('list')->willReturn(new ArrayCollection([
            new Absence('626e9b71-54f6-44fd-9539-0120cf37daf6',
                '626e9b71-54f6-44fd-9539-0120cf37daf6',
                new \DateTime('2021-01-01'),
                new \DateTime('2021-01-02'),
                'CP'),
            new Absence('35c5382b-04c8-4555-aa5c-d07631ef19b5',
                '35c5382b-04c8-4555-aa5c-d07631ef19b5',
                new \DateTime('2021-01-01'),
                new \DateTime('2021-01-02'),
                'CP'),
        ]));
        $controller = new AbsenceController($repository, $employeeRepository);

        $response = $controller->list();

        self::assertThat($response, self::isInstanceOf(JsonResponse::class));
        self::assertThat($response->getContent(), self::equalTo('[{"id":"626e9b71-54f6-44fd-9539-0120cf37daf6","employee":"626e9b71-54f6-44fd-9539-0120cf37daf6","startDate":{"date":"2021-01-01 00:00:00.000000","timezone_type":3,"timezone":"UTC"},"endDate":{"date":"2021-01-02 00:00:00.000000","timezone_type":3,"timezone":"UTC"},"type":"CP"},{"id":"35c5382b-04c8-4555-aa5c-d07631ef19b5","employee":"35c5382b-04c8-4555-aa5c-d07631ef19b5","startDate":{"date":"2021-01-01 00:00:00.000000","timezone_type":3,"timezone":"UTC"},"endDate":{"date":"2021-01-02 00:00:00.000000","timezone_type":3,"timezone":"UTC"},"type":"CP"}]'));
    }

    /**
     * @test
     * @throws Exception
     * @throws \JsonException
     */
    public function should_get_absence_from_id(): void
    {
        $repository = $this->createMock(AbsenceRepository::class);
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $repository->method('get')->willReturn(
            new Absence('626e9b71-54f6-44fd-9539-0120cf37daf6',
                '626e9b71-54f6-44fd-9539-0120cf37daf6',
                new \DateTime('2021-01-01'),
                new \DateTime('2021-01-02'),
                'CP'),
        );
        $controller = new AbsenceController($repository,$employeeRepository);

        $response = $controller->get(1);

        self::assertThat($response, self::isInstanceOf(JsonResponse::class));
        self::assertThat($response->getContent(), self::equalTo('{"id":"626e9b71-54f6-44fd-9539-0120cf37daf6","employee":"626e9b71-54f6-44fd-9539-0120cf37daf6","startDate":{"date":"2021-01-01 00:00:00.000000","timezone_type":3,"timezone":"UTC"},"endDate":{"date":"2021-01-02 00:00:00.000000","timezone_type":3,"timezone":"UTC"},"type":"CP"}'));
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function should_return_HTTP_status_201_add_absence()
    {
        $repository = $this->createMock(AbsenceRepository::class);
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $repository->method('add');
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"employee":"626e9b71-54f6-44fd-9539-0120cf37daf6","startDate":"2021-01-01","endDate":"2021-01-02","type":"CP"}');

        $controller = new AbsenceController($repository,$employeeRepository);

        $response = $controller->add($request);

        self::assertThat($response->getStatusCode(), self::equalTo(Response::HTTP_CREATED));
    }

    /**
     * @test
     */
    public function should_return_HTTP_status_500__when_add_absence_failed()
    {
        $repository = $this->createMock(AbsenceRepository::class);
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $repository->method('add')->willThrowException(new Exception());
        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"employee":"626e9b71-54f6-44fd-9539-0120cf37daf6","startDate":"2021-01-01","endDate":"2021-01-02","type":"CP"}');

        $controller = new AbsenceController($repository,$employeeRepository);

        $response = $controller->add($request);

        self::assertThat($response->getStatusCode(), self::equalTo(Response::HTTP_INTERNAL_SERVER_ERROR));
    }

    /**
     * @test
     */
    public function should_delete_absence()
    {
        $repository = $this->createMock(AbsenceRepository::class);
        $employeeRepository = $this->createMock(EmployeeRepository::class);
        $repository->method('delete')->willReturn(1);

        $controller = new AbsenceController($repository,$employeeRepository);

        $response = $controller->delete('626e9b71-54f6-44fd-9539-0120cf37daf6');

        self::assertThat($response->getStatusCode(), self::equalTo(Response::HTTP_OK));
    }
}
