<?php

namespace App;

use DateTime;
use Doctrine\DBAL\Exception;
use JsonException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AbsenceController implements Controller
{
    /**
     * @var AbsenceRepository
     */
    private AbsenceRepository $repository;

    private const VACATION_TYPE = 'CP';

    private const COMPENSATORY_TIME_TYPE = 'RTT';

    private EmployeeRepository $employeeRepository;

    /**
     * @throws JsonException
     */
    public function __construct(AbsenceRepository $repository, EmployeeRepository $employeeRepository)
    {
        $this->repository = $repository;
        $this->employeeRepository = $employeeRepository;
        $this->holidays = json_decode(file_get_contents(self::URL), false, 512, JSON_THROW_ON_ERROR);
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
     */
    public function get(string $id): JsonResponse
    {
        return new JsonResponse($this->repository->get($id));
    }

    /**
     * @throws Throwable
     * @throws JsonException
     */
    public function add(Request $request): JsonResponse
    {
        $absence = $this->absenceMap($request->getContent());
        try {
            if ($this->employeeRepository->get($absence->getEmployee())) {
                $absenceEmployee = $this->employeeRepository->get($absence->getEmployee());
                if ($absence->getType() === self::VACATION_TYPE) {
                    if ($absenceEmployee->getVacationDays() >= $absence->getDays()) {
                        $absenceEmployee->setVacationDays($absenceEmployee->getVacationDays() - $absence->getDays());
                        $this->employeeRepository->update($absenceEmployee);
                        $this->repository->add($absence);
                    } else {
                        return new JsonResponse('Not enough vacation days', Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else if ($absence->getType() === self::COMPENSATORY_TIME_TYPE) {
                    if ($absenceEmployee->getCompensatoryTimeDays() >= $absence->getDays()) {
                        $absenceEmployee->setCompensatoryTimeDays(
                            $absenceEmployee->getCompensatoryTimeDays() - $absence->getDays());
                        $this->employeeRepository->update($absenceEmployee);
                        $this->repository->add($absence);
                    } else {
                        return new JsonResponse('Not enough compensatory time', Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }
        } catch (\Exception $e) {
            return new JsonResponse('Error during add', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse('Add successful', Response::HTTP_CREATED);
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    public function delete(string $id): JsonResponse
    {
        try {
            $absence = $this->repository->get($id);
            $absenceEmployee = $this->employeeRepository->get($absence->getEmployee());
            if ($absence->getType() === self::VACATION_TYPE) {
                if ($absenceEmployee->getVacationDays() + $absence->getDays() <= 25) {
                    $absenceEmployee->setVacationDays($absenceEmployee->getVacationDays() + $absence->getDays());
                    $this->employeeRepository->update($absenceEmployee);
                } else {
                    return new JsonResponse('Too many vacation days', Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else if ($absence->getType() === self::COMPENSATORY_TIME_TYPE) {
                if ($absenceEmployee->getCompensatoryTimeDays() + $absence->getDays() <= 10) {
                    $absenceEmployee->setCompensatoryTimeDays(
                        $absenceEmployee->getCompensatoryTimeDays() + $absence->getDays());
                    $this->employeeRepository->update($absenceEmployee);
                } else {
                    return new JsonResponse('Too many compensatory time', Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
            $this->repository->delete($id);
        } catch (\Exception $e) {
            return new JsonResponse('Error during delete', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse('Delete successful', Response::HTTP_OK);
    }

    /**
     * @throws JsonException
     * @throws \Exception
     */
    public function absenceMap(string $json): Absence
    {
        $absence = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        return new Absence(
            Uuid::uuid4()->toString(),
            $absence->employee,
            new DateTime($absence->startDate),
            new DateTime($absence->endDate),
            $absence->type,
        );
    }

}
