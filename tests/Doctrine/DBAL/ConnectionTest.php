<?php


namespace Doctrine\DBAL;


use DBUtils\FileDB;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public const DB_FILE_PATH = __DIR__ . '/../../testdb.sqlite';

    private $connectionParams;

    /**
     * @before
     */
    public function init()
    {
        FileDB::initializeDB(self::DB_FILE_PATH);

        $this->connectionParams = [
            'url' => 'sqlite:///'.self::DB_FILE_PATH
        ];
    }

    /**
     * @test
     */
    public function should_connect_to_database()
    {
        $connection = DriverManager::getConnection($this->connectionParams);

        self::assertThat($connection, self::isInstanceOf(Connection::class));
    }

    /**
     * @test
     */
    public function should_create_and_connect_to_database()
    {
        $connection = DriverManager::getConnection($this->connectionParams);
        $schemaManager = $connection->getSchemaManager();
        $tables = $schemaManager->listTables();

        self::assertThat($connection, self::isInstanceOf(Connection::class));
        self::assertThat($tables, self::isEmpty());
    }
}
