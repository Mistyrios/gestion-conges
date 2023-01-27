<?php


namespace App;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Ramsey\Uuid\Uuid;

class EmployeeRepository
{
    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * EmployeeRepository constructor.
     */
    public function __construct(Connection $connection)
    {

        $this->connection = $connection;
    }

    /**
     * @throws \Throwable
     */
    public function add(Employee $employee): void
    {
        $this->connection->transactional(function ($conn) use ($employee) {
            $qb = $conn->createQueryBuilder();
            $qb->insert('employee')
                ->setValue('id', '?')
                ->setValue('firstname', '?')
                ->setValue('lastname', '?')
                ->setValue('vacationDays', '?')
                ->setValue('compensatoryTimeDays', '?')
                ->setParameter(0, $employee->getId())
                ->setParameter(1, $employee->getFirstName())
                ->setParameter(2, $employee->getLastName())
                ->setParameter(3, $employee->getVacationDays())
                ->setParameter(4, $employee->getCompensatoryTimeDays());
            $qb->execute();
        });
    }

    /**
     * @throws \Throwable
     */
    public function update(Employee $employee): void
    {
        $this->connection->transactional(function ($conn) use ($employee) {
            $qb = $conn->createQueryBuilder();
            $qb->update('employee')
                ->set('firstname', '?')
                ->set('lastname', '?')
                ->set('vacationDays', '?')
                ->set('compensatoryTimeDays', '?')
                ->where('id = ?')
                ->setParameter(0, $employee->getFirstName())
                ->setParameter(1, $employee->getLastName())
                ->setParameter(2, $employee->getVacationDays())
                ->setParameter(3, $employee->getCompensatoryTimeDays())
                ->setParameter(4, $employee->getId());
            $qb->execute();
        });
    }

    /**
     * @throws Exception
     */
    public function list(): ArrayCollection
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('id', 'firstname', 'lastname', 'vacationDays', 'compensatoryTimeDays')
            ->from('employee')
            ->executeQuery();
        return $this->mapList($statement->fetchAllAssociative());
    }

    /**
     * @throws Exception
     */
    public function get(string $id): ?Employee
    {
        $SQL = $this->connection->createQueryBuilder()
            ->select('id', 'firstname', 'lastname', 'vacationDays', 'compensatoryTimeDays')
            ->from('employee')
            ->where('id = :id')
            ->getSQL();
        $statement = $this->connection->prepare($SQL);
        $result = $statement->executeQuery(['id' => $id]);
        if ($result->rowCount() === 0) {
            return null;
        }
        $record = $result->fetchAssociative();
        return $this->map($record);
    }

    /**
     * @throws Exception
     */
    public function delete(string $id):int
    {
        $qb = $this->connection->createQueryBuilder()
            ->delete('employee')
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

    private function map($record): Employee
    {
        return new Employee(
            $record['id'],
            $record['firstname'],
            $record['lastname'],
            $record['vacationDays'],
            $record['compensatoryTimeDays']
        );
    }
}
