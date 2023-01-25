<?php


namespace DI;


use App\EmployeeRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public const DB_FILE_PATH = __DIR__ . '/../../testdb.sqlite';

    /**
     * @test
     */
    public function should_define_container_directly()
    {
        $container = new Container();
        $container->set('connection.params', [
            'url' => 'sqlite:///' . self::DB_FILE_PATH
        ]);
        $container->set('Connection', function (Container $c) {
            return DriverManager::getConnection($c->get('connection.params'));
        });

        $connection = $container->get('Connection');

        self::assertThat($connection, self::isInstanceOf(Connection::class));
    }

    /**
     * @test
     */
    public function should_define_container_from_builder() {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            'connection.params' => [
                'url' => 'sqlite:///' . self::DB_FILE_PATH
            ],
            'Connection' => function(Container $c) {
                return DriverManager::getConnection($c->get('connection.params'));
            }
        ]);
        $container = $containerBuilder->build();

        $connection = $container->get('Connection');

        self::assertThat($connection, self::isInstanceOf(Connection::class));
    }

    /**
     * @test
     */
    public function should_use_type_hinting_for_injection() {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            'connection.params' => [
                'url' => 'sqlite:///' . self::DB_FILE_PATH
            ],
            Connection::class => function(Container $c) {
                return DriverManager::getConnection($c->get('connection.params'));
            },
            EmployeeRepository::class => function(Connection $connection) {
                return new EmployeeRepository($connection);
            }
        ]);
        $container = $containerBuilder->build();

        $connection = $container->get(EmployeeRepository::class);

        self::assertThat($connection, self::isInstanceOf(EmployeeRepository::class));
    }
}
