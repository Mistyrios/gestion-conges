<?php

namespace App;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use DateTime;

class AbsenceRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws \Throwable
     */
    public function add(Absence $absence): void
    {
        $this->connection->transactional(function ($conn) use ($absence) {
            $qb = $conn->createQueryBuilder();
            $qb->insert('absence')
                ->setValue('id', '?')
                ->setValue('employee', '?')
                ->setValue('startDate', '?')
                ->setValue('endDate', '?')
                ->setValue('type', '?')
                ->setParameter(0, $absence->getId())
                ->setParameter(1, $absence->getEmployee())
                ->setParameter(2, $absence->getStartDate()->format('Y-m-d'))
                ->setParameter(3, $absence->getEndDate()->format('Y-m-d'))
                ->setParameter(4, $absence->getType());
            $qb->execute();
        });
    }

    /**
     * @throws Exception
     */
    public function list(): ArrayCollection
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('id', 'employee', 'startDate', 'endDate', 'type')
            ->from('absence')
            ->executeQuery();
        return $this->mapList($statement->fetchAllAssociative());

    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function get(string $id): Absence
    {
        $SQL = $this->connection->createQueryBuilder()
            ->select('id', 'employee', 'startDate', 'endDate', 'type')
            ->from('absence')
            ->where('id = :id')
            ->getSQL();
        $statement = $this->connection->prepare($SQL);
        $result = $statement->executeQuery(['id' => $id]);
        $record = $result->fetchAssociative();
        return $this->map($record);
    }

    /**
     * @throws Exception
     */
    public function delete(string $id): int
    {
        $qb = $this->connection->createQueryBuilder()
            ->delete('absence')
            ->where('id = :id')
            ->setParameter('id', $id);
        return $qb->executeStatement();
    }

    private function mapList($records): ArrayCollection
    {
        return (new ArrayCollection($records))->map(function ($record) {
            return $this->map($record);
        });
    }

    /**
     * @throws \Exception
     */
    private function map($record): Absence
    {
        return new Absence(
            $record['id'],
            $record['employee'],
            new DateTime($record['startDate']),
            new DateTime($record['endDate']),
            $record['type']
        );
    }

    /**
     * @param string $employeeId
     * @param string $startDate
     * @param string $endDate
     * @return bool
     * @throws Exception
     */
    public function checkPeriodConflict(string $employeeId, string $startDate, string $endDate): bool
    {
        $SQL = $this->connection->createQueryBuilder()
            ->select('COUNT(*) as count')
            ->from('absence')
            ->where('employee = :employeeId')
            ->andWhere('(startDate <= :startDate AND endDate >= :startDate) OR (startDate <= :endDate AND endDate >= :endDate) OR (startDate >= :startDate AND endDate <= :endDate)')
            ->getSQL();
        $statement = $this->connection->prepare($SQL);
        $result = $statement->executeQuery(['employeeId' => $employeeId, 'startDate' => $startDate, 'endDate' => $endDate]);
        $record = $result->fetchAssociative();
        return $record['count'] > 0;
    }


}
