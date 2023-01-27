<?php

namespace App;

use DateTime;
use DBUtils\FileDB;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use PDO;
use PHPUnit\Framework\TestCase;

class AbsenceRepositoryTest extends TestCase
{
    private $container;

    private Connection $pdoConnection;

    /**
     * @before
     * @throws Exception
     */
    public function init(): void
    {
        $this->container = include __DIR__ . '/../../app/bootstrap_test.php';

        FileDB::initializeDB($this->container->get('db.file'));
        $this->pdoConnection = DriverManager::getConnection($this->container->get('connection.params'));
        $this->pdoConnection->executeStatement("CREATE TABLE absence (
            id VARCHAR(36) PRIMARY KEY, 
            employee VARCHAR(36) NOT NULL, 
            startDate DATE NOT NULL,
            endDate DATE NOT NULL,
            type VARCHAR(255) NOT NULL)");
        $this->pdoConnection->executeStatement("INSERT INTO absence (id, employee, startDate, endDate, type) VALUES ('626e9b71-54f6-44fd-9539-0120cf37daf6', '626e9b71-54f6-44fd-9539-0120cf37daf6', '2021-01-01', '2021-01-02', 'CP')");
        $this->pdoConnection->executeStatement("INSERT INTO absence (id, employee, startDate, endDate, type) VALUES ('35c5382b-04c8-4555-aa5c-d07631ef19b5', '35c5382b-04c8-4555-aa5c-d07631ef19b5', '2021-01-01', '2021-01-02', 'CP')");
    }

    /**
     * @test
     */
    public function should_list_absences_from_absence_table(): void
    {
        $repository = new AbsenceRepository($this->container->get('Connection'));
        $absences = $repository->list();

        self::assertThat($absences, self::countOf(2));
        self::assertThat($absences[0], self::isInstanceOf(Absence::class));
    }

    /**
     * @test
     */
    public function should_get_absence_from_id(): void
    {
        $repository = new AbsenceRepository($this->container->get('Connection'));
        $absence = $repository->get('626e9b71-54f6-44fd-9539-0120cf37daf6');

        self::assertThat($absence, self::isInstanceOf(Absence::class));
        self::assertThat($absence->getId(), self::equalTo('626e9b71-54f6-44fd-9539-0120cf37daf6'));
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function should_add_absence_to_absence_table(): void
    {
        $repository = new AbsenceRepository($this->container->get('Connection'));
        $repository->add(new Absence(
            '35c5382b-04c8-4225-aa5c-d07631ef19b5',
            '4ef3e75e-bdae-4fbe-8584-f21fbb39bb2f',
            new DateTime('2021-01-01'),
            new DateTime('2021-01-02'),
            'CP'
        ));

        $record = $this->pdoConnection
            ->executeQuery("SELECT id, employee, startDate, endDate, type FROM absence WHERE id ='35c5382b-04c8-4225-aa5c-d07631ef19b5'")
            ->fetchAssociative(PDO::FETCH_ASSOC);
        self::assertThat($record['id'], self::equalTo('35c5382b-04c8-4225-aa5c-d07631ef19b5'));
        self::assertThat($record['employee'], self::equalTo('4ef3e75e-bdae-4fbe-8584-f21fbb39bb2f'));
        self::assertThat($record['startDate'], self::equalTo('2021-01-01'));
        self::assertThat($record['endDate'], self::equalTo('2021-01-02'));
        self::assertThat($record['type'], self::equalTo('CP'));

    }

    /**
     * @test
     * @throws Exception
     */
    public function should_delete_absence_from_absence_table(): void
    {
        $repository = new AbsenceRepository($this->container->get('Connection'));

        $affectedRows = $repository->delete('626e9b71-54f6-44fd-9539-0120cf37daf6');

        self::assertThat($affectedRows, self::equalTo(1));
        $records = $this->pdoConnection
            ->executeQuery("SELECT id, employee, startDate, endDate, type FROM absence")
            ->fetchAllAssociative();
        self::assertThat($records, self::countOf(1));
    }

    /**
     * @test
     */
    public function should_throw_exception_on_integrity_constraint_violation(): void
    {
        $repository = new AbsenceRepository($this->container->get('Connection'));

        $this->expectException(Exception::class);
        $repository->add(new Absence(
            '626e9b71-54f6-44fd-9539-0120cf37daf6',
            '4ef3e75e-bdae-4fbe-8584-f21fbb39bb2f',
            new DateTime('2021-01-01'),
            new DateTime('2021-01-02'),
            'CP'
        ));
    }

    /**
     * @test
     */
    public function should_throw_exception_when_delete_unknown_absence(): void
    {
        $repository = new AbsenceRepository($this->container->get('Connection'));

        $affectedRows = $repository->delete('4ef3e75e-bdae-4fbe-8584-f21fbb39bb2f');

        self::assertThat($affectedRows, self::equalTo(0));
    }
}
