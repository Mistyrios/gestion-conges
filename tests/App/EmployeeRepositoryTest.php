<?php

namespace App;

use DBUtils\FileDB;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;

class EmployeeRepositoryTest extends TestCase
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
        $this->pdoConnection->executeStatement("CREATE TABLE employee (
            id VARCHAR(36) PRIMARY KEY, 
            firstname VARCHAR(255) NOT NULL, 
            lastname VARCHAR(255) NOT NULL,
            vacationDays INT NOT NULL,
            compensatoryTimeDays INT NOT NULL)");
        $this->pdoConnection->executeStatement("INSERT INTO employee (id, firstname, lastname, vacationDays, compensatoryTimeDays) VALUES ('626e9b71-54f6-44fd-9539-0120cf37daf6', 'John', 'Doe', 8, 1)");
        $this->pdoConnection->executeStatement("INSERT INTO employee (id, firstname, lastname, vacationDays, compensatoryTimeDays) VALUES ('35c5382b-04c8-4555-aa5c-d07631ef19b5', 'Jane', 'Doe', 8, 1)");
    }

    /**
     * @test
     */
    public function should_list_employees_from_employee_table(): void
    {
        $repository = new EmployeeRepository($this->container->get('Connection'));
        $employees = $repository->list();

        self::assertThat($employees, self::countOf(2));
        self::assertThat($employees[0], self::isInstanceOf(Employee::class));
    }

    /**
     * @test
     */
    public function should_get_employee_from_id(): void
    {
        $repository = new EmployeeRepository($this->container->get('Connection'));
        $employee = $repository->get('626e9b71-54f6-44fd-9539-0120cf37daf6');

        self::assertThat($employee, self::isInstanceOf(Employee::class));
        self::assertThat($employee->getId(), self::equalTo('626e9b71-54f6-44fd-9539-0120cf37daf6'));
    }

    /**
     * @test
     */
    public function should_add_employee_to_employee_table(): void
    {
        $repository = new EmployeeRepository($this->container->get('Connection'));

        $repository->add(new Employee('4ef3e75e-bdae-4fbe-8584-f21fbb39bb2f', 'Robert', 'Paulson', 8, 1));

        $record = $this->pdoConnection
            ->executeQuery("SELECT id, firstname, lastname, vacationDays, compensatoryTimeDays FROM employee WHERE id ='4ef3e75e-bdae-4fbe-8584-f21fbb39bb2f'")
            ->fetchAssociative(\PDO::FETCH_ASSOC);
        self::assertThat($record['id'], self::equalTo('4ef3e75e-bdae-4fbe-8584-f21fbb39bb2f'));
        self::assertThat($record['firstname'], self::equalTo('Robert'));
        self::assertThat($record['lastname'], self::equalTo('Paulson'));
        self::assertThat($record['vacationDays'], self::equalTo('8'));
        self::assertThat($record['compensatoryTimeDays'], self::equalTo('1'));
    }

    /**
     * @test
     */
    public function should_add_and_update_employee_to_employee_table(): void
    {
        $repository = new EmployeeRepository($this->container->get('Connection'));

        $repository->add(new Employee('4ef3e75e-bdae-4fbe-8584-f21fbb39bb2a', 'Robert', 'Paulson', 8, 1));
        $repository->update(new Employee('4ef3e75e-bdae-4fbe-8584-f21fbb39bb2a', 'Romain', 'Allemand', 9, 2));

        $record = $this->pdoConnection
            ->executeQuery("SELECT id, firstname, lastname, vacationDays, compensatoryTimeDays FROM employee WHERE id ='4ef3e75e-bdae-4fbe-8584-f21fbb39bb2a'")
            ->fetchAssociative(\PDO::FETCH_ASSOC);
        self::assertThat($record['id'], self::equalTo('4ef3e75e-bdae-4fbe-8584-f21fbb39bb2a'));
        self::assertThat($record['firstname'], self::equalTo('Romain'));
        self::assertThat($record['lastname'], self::equalTo('Allemand'));
        self::assertThat($record['vacationDays'], self::equalTo('9'));
        self::assertThat($record['compensatoryTimeDays'], self::equalTo('2'));
    }

    /**
     * @test
     */
    public function should_delete_employee_from_employee_table(): void
    {
        $repository = new EmployeeRepository($this->container->get('Connection'));

        $affectedRows = $repository->delete('626e9b71-54f6-44fd-9539-0120cf37daf6');

        self::assertThat($affectedRows, self::equalTo(1));
        $records = $this->pdoConnection
            ->executeQuery("SELECT id, firstname, lastname, vacationDays, compensatoryTimeDays FROM employee")
            ->fetchAllAssociative();
        self::assertThat($records, self::countOf(1));
    }

    /**
     * @test
     */
    public function should_throw_exception_on_integrity_constraint_violation(): void
    {
        $repository = new EmployeeRepository($this->container->get('Connection'));

        $this->expectException(Exception::class);
        $repository->add(new Employee('35c5382b-04c8-4555-aa5c-d07631ef19b5', 'Robert', 'Paulson', 8, 1));
    }

    /**
     * @test
     */
    public function should_throw_exception_when_delete_unknown_employee(): void
    {
        $repository = new EmployeeRepository($this->container->get('Connection'));

        $affectedRows = $repository->delete('4ef3e75e-bdae-4fbe-8584-f21fbb39bb2f');

        self::assertThat($affectedRows, self::equalTo(0));
    }
}
