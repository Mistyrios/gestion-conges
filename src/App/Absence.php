<?php

namespace App;
use DateTime;
use JsonSerializable;

class Absence implements JsonSerializable
{
    private string $id;

    private string $employee;
    private DateTime $startDate;
    private Datetime $endDate;

    private string $type;
    private const ABSENCE_TYPES = [
        'RTT',
        'CP'
    ];

    public function __construct(string $id, string $employee, DateTime $startDate, DateTime $endDate, string $type)
    {
        $this->typeIsValid($type);
        $this->id = $id;
        $this->employee = $employee;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmployee(): string
    {
        return $this->employee;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return DateTime
     */
    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    public function typeIsValid(string $type): void
    {
        if (!in_array($type, self::ABSENCE_TYPES)) {
            throw new \RuntimeException('Invalid absence type');
        }
    }

    public function getDays(): int
    {
        if ($this->startDate->format('Y-m-d') === $this->endDate->format('Y-m-d')) {
            return 1;
        }
        return $this->startDate->diff($this->endDate)->days + 1;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'employee' => $this->employee,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'type' => $this->type
        ];
    }
}
