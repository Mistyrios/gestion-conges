<?php


namespace Doctrine\DBAL;


use DBUtils\FileDB;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Schema\SchemaException;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{

    public const DB_FILE_PATH = __DIR__ . '/../../testdb.sqlite';

    private Connection $connection;

    private Schema $schema;

    /**
     * @before
     * @throws Exception
     */
    public function init(): void
    {
        FileDB::initializeDB(self::DB_FILE_PATH);

        $connectionParams = [
            'url' => 'sqlite:///'.self::DB_FILE_PATH
        ];
        $this->connection = DriverManager::getConnection($connectionParams);
        $schemaManager = $this->connection->createSchemaManager();
        $this->schema = $schemaManager->createSchema();
    }

    /**
     * @test
     */
    public function should_generate_SQL_to_create_table(): void
    {
        $table = $this->schema->createTable("employee");
        $table->addColumn('username', 'string');

        $requests = $this->schema->toSql(new SqlitePlatform());

        self::assertThat($requests, self::countOf(1));
        self::assertThat($requests[0], self::equalTo('CREATE TABLE employee (username VARCHAR(255) NOT NULL)'));

    }

    /**
     * @test
     * @throws Exception
     */
    public function should_do_schema_migration(): void
    {
        $userTable = $this->schema->createTable("employee");
        $userTable->addColumn('id', 'string');
        $userTable->addColumn('firstname', 'string');
        $userTable->addColumn('lastname', 'string');
        $userTable->addColumn('vacationDays', 'integer');
        $userTable->addColumn('compensatoryTimeDays', 'integer');
        $requests = $this->schema->toSql(new SqlitePlatform());

        $this->connection->executeQuery($requests[0]);

        $userTable = $this->connection->executeQuery("SELECT name FROM sqlite_master WHERE type ='table' AND name NOT LIKE 'sqlite_%'")->fetchAllAssociative();

        $absenceTable = $this->schema->createTable("absence");
        $absenceTable->addColumn('id', 'string');
        $absenceTable->addColumn('employee', 'string');
        $absenceTable->addColumn('type', 'string');
        $absenceTable->addColumn('startDate', 'string');
        $absenceTable->addColumn('endDate', 'string');
        $requests = $this->schema->toSql(new SqlitePlatform());

        $this->connection->executeQuery($requests[1]);

        $absenceTable = $this->connection->executeQuery("SELECT name FROM sqlite_master WHERE type ='table' AND name NOT LIKE 'sqlite_%'")->fetchAllAssociative();

        self::assertThat($absenceTable[1]['name'], self::equalTo('absence'));
        self::assertThat($userTable[0]['name'], self::equalTo('employee'));
    }
}
