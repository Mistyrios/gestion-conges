<?php


namespace App;


use http\Exception;
use RuntimeException;

class Employee implements \JsonSerializable
{
    /**
     * @var string
     */
    private string $id;
    /**
     * @var string
     */
    private string $firstName;
    /**
     * @var string
     */
    private string $lastName;

    private int $vacationDays;

    private int $compensatoryTimeDays;

    public function __construct(string $id, string $firstName, string $lastName, int $vacationDays, int $compensatoryTimeDays)
    {
        $this->dataIsValid($vacationDays, $compensatoryTimeDays);
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->vacationDays = $vacationDays;
        $this->compensatoryTimeDays = $compensatoryTimeDays;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getVacationDays(): int
    {
        return $this->vacationDays;
    }

    public function getCompensatoryTimeDays(): int
    {
        return $this->compensatoryTimeDays;
    }

    /**
     * @param int $vacationDays
     */
    public function setVacationDays(int $vacationDays): void
    {
        $this->vacationDays = $vacationDays;
    }

    /**
     * @param int $compensatoryTimeDays
     */
    public function setCompensatoryTimeDays(int $compensatoryTimeDays): void
    {
        $this->compensatoryTimeDays = $compensatoryTimeDays;
    }


    public function dataIsValid(int $vacationDays, int $compensatoryTimeDays): void
    {
        if ($vacationDays < 0 || $compensatoryTimeDays < 0
            || $vacationDays > 25 || $compensatoryTimeDays > 10) {
            throw new RuntimeException(
                'Invalid data. Vacation days must be between 0 and 25 and 
                compensatory time days must be between 0 and 10');
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'vacationDays' => $this->vacationDays,
            'compensatoryTimeDays' => $this->compensatoryTimeDays
        ];
    }
}
