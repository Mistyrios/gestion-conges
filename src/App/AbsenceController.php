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

    private const API_KEY = "246d88731d741558c8437eaffc7584f6598c8d2c";
    private const COUNTRY = 'FR';
    private const YEAR = '2023';
    private const URL = 'https://calendarific.com/api/v2/holidays?api_key=' . self::API_KEY . '&country=' . self::COUNTRY . '&year=' . self::YEAR . '&type=national';

    private array $holidays;


    /**
     * @throws JsonException
     */
    public function __construct(AbsenceRepository $repository, EmployeeRepository $employeeRepository)
    {
        $this->repository = $repository;
        $this->employeeRepository = $employeeRepository;
        $this->holidays = $this->getHolidays();
    }

    /**
     * @throws JsonException
     */
    public function getHolidays(): array
    {
        $holidays = [];
        $holidaysResponses = json_decode(file_get_contents(self::URL), false, 512, JSON_THROW_ON_ERROR);
        foreach ($holidaysResponses->response->holidays as $holidaysResponse) {
            try {
                $tempDate = new DateTime($holidaysResponse->date->iso);
                $holidays[$holidaysResponse->name] = $tempDate->format('Y-m-d');
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
        return $holidays;
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
            $absenceEmployee = $this->employeeRepository->get($absence->getEmployee());
            if ($absenceEmployee) {
                $workingDays = $this->countWorkingDays($absence);

                if ($absence->getType() === self::VACATION_TYPE) {
                    if ($absenceEmployee->getVacationDays() >= $workingDays) {
                        $absenceEmployee->setVacationDays($absenceEmployee->getVacationDays() - $workingDays);
                        $this->employeeRepository->update($absenceEmployee);
                        $this->repository->add($absence);
                    } else {
                        return new JsonResponse('Not enough vacation days', Response::HTTP_INTERNAL_SERVER_ERROR);
                    }

                } else if ($absence->getType() === self::COMPENSATORY_TIME_TYPE) {
                    if ($absenceEmployee->getCompensatoryTimeDays() >= $workingDays) {
                        $absenceEmployee->setCompensatoryTimeDays(
                            $absenceEmployee->getCompensatoryTimeDays() - $workingDays);
                        $this->employeeRepository->update($absenceEmployee);
                        $this->repository->add($absence);
                    } else {
                        return new JsonResponse('Not enough compensatory time', Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            } else {
                return new JsonResponse('Employee not found', Response::HTTP_INTERNAL_SERVER_ERROR);
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
            if ($absenceEmployee) {
                $workingDays = $this->countWorkingDays($absence);

                if ($absence->getType() === self::VACATION_TYPE) {
                    if ($absenceEmployee->getVacationDays() + $workingDays <= 25) {
                        $absenceEmployee->setVacationDays($absenceEmployee->getVacationDays() + $workingDays);
                        $this->employeeRepository->update($absenceEmployee);
                        $this->repository->delete($id);
                    } else {
                        return new JsonResponse('Too many vacation days', Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else if ($absence->getType() === self::COMPENSATORY_TIME_TYPE) {
                    if ($absenceEmployee->getCompensatoryTimeDays() + $workingDays <= 10) {
                        $absenceEmployee->setCompensatoryTimeDays(
                            $absenceEmployee->getCompensatoryTimeDays() + $workingDays);
                        $this->employeeRepository->update($absenceEmployee);
                        $this->repository->delete($id);
                    } else {
                        return new JsonResponse('Too many compensatory time days', Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }
            else {
                return new JsonResponse('Employee not found', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
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
        $this->checkDatesValidity($absence->employee, $absence->startDate, $absence->endDate);
        return new Absence(
            Uuid::uuid4()->toString(),
            $absence->employee,
            new DateTime($absence->startDate),
            new DateTime($absence->endDate),
            $absence->type,
        );
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function checkDatesValidity(string $employeeId, string $startDate, string $endDate): void
    {
        $isStartDateValid = (bool)strtotime($startDate) && date("Y-m-d", strtotime($startDate)) === $startDate;
        $isEndDateValid = (bool)strtotime($endDate) && date("Y-m-d", strtotime($endDate)) === $endDate;
        if (new DateTime($startDate) < new DateTime()) {
            throw new Exception('Start date cannot be in the past');
        }
        if (!$isStartDateValid || !$isEndDateValid) {
            throw new Exception('Invalid date format');
        }
        if (new DateTime($startDate) > new DateTime($endDate)) {
            throw new Exception('Start date cannot be greater than end date');
        }
        if ($this->checkPeriodConflict($employeeId, $startDate, $endDate)) {
            throw new \RuntimeException('There is already an absence planned during this period');
        }
    }


    /**
     * @throws Exception
     */
    public function checkPeriodConflict(string $employeeId, string $startDate, string $endDate): bool
    {
        //verify if the employee is defined
        $employee = $this->employeeRepository->get($employeeId);
        if ($employee === null) {
            throw new \RuntimeException('Employee not found');
        }
        return $this->repository->checkPeriodConflict($employeeId, $startDate, $endDate);
    }

    /**
     * @param Absence $absence
     * @return int
     */
    public function countWorkingDays(Absence $absence): int
    {
        $startDate = clone $absence->getStartDate();
        $endDate = clone $absence->getEndDate();
        $days = $absence->getDays();

        // Check if the absence days match with holidays or weekends
        $holidaysCount = 0;
        $weekendsCount = 0;

        while ($startDate <= $endDate) {
            if (in_array($startDate, $this->holidays, false)) {
                $holidaysCount++;
            } elseif ($startDate->format('N') >= 6) {
                $weekendsCount++;
            }
            $startDate->modify('+1 day');
        }

        // Remove holidays and weekends from absence days
        $days -= $holidaysCount + $weekendsCount;
        return $days;
    }
}
