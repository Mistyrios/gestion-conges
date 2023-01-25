<?php


namespace App;


use Doctrine\DBAL\Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController implements Controller
{
    /**
     * @var EmployeeRepository
     */
    private EmployeeRepository $repository;

    public function __construct(EmployeeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function list(): JsonResponse
    {
        $response = new JsonResponse();
        $response->setData($this->repository->list()->toArray());
        return $response;
    }

    /**
     * @throws Exception
     *
     */
    public function get(string $id): JsonResponse
    {
        return new JsonResponse($this->repository->get($id));
    }

    /**
     * @throws \Throwable
     * @throws \JsonException
     */
    public function add(Request $request): JsonResponse
    {
        $employee = $this->employeeMap($request->getContent());
        try {
            $this->repository->add($employee);
        } catch (\Exception $e) {
            return new JsonResponse('Error during add', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse('Add successful', Response::HTTP_CREATED);
    }

    /**
     * @throws \JsonException
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $employee = $this->employeeMap($request->getContent(), $id);

        try {
            $this->repository->update($employee);
        } catch (\Exception|\Throwable $e) {
            return new JsonResponse('Error during update', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse('Update successful', Response::HTTP_OK);
    }

    public function delete(string $id): JsonResponse
    {
        try {
            $this->repository->delete($id);
        } catch (\Exception $e) {
            return new JsonResponse('Error during delete', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse('Delete successful', Response::HTTP_OK);
    }

    /**
     * @throws \JsonException
     */
    private function employeeMap(string $json, string $id = null): Employee
    {
        $employee = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        if ($employee->vacationDays > 0 && $employee->compensatoryTimeDays > 0
            && $employee->vacationDays <= 25 && $employee->compensatoryTimeDays <= 10) {
            return new Employee(
                $id ?: Uuid::uuid4()->toString(),
                $employee->firstName,
                $employee->lastName,
                $employee->vacationDays,
                $employee->compensatoryTimeDays,
            );
        }
        throw new \InvalidArgumentException(
            'Invalid data. Vacation days must be between 0 and 25 and compensatory time days must be between 0 and 10');
    }
}
