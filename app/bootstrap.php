<?php

use App\AbsenceController;
use App\AbsenceRepository;
use App\Router;
use App\EmployeeController;
use App\EmployeeRepository;
use DI\Container;
use Doctrine\DBAL\DriverManager;

include __DIR__.'/../vendor/autoload.php';
$holidaysDates = include __DIR__.'/holidays.php';

$container = new Container();

$container->set('db.file', __DIR__ . '/../src/db.sqlite');

$container->set('connection.params', [
    'url' => 'sqlite:///' . $container->get('db.file')
]);
$container->set('Connection', function (Container $c) {
    return DriverManager::getConnection($c->get('connection.params'));
});

$container->set('EmployeeRepository' , function (Container $c){
    return new EmployeeRepository($c->get('Connection'));
});

$container->set('EmployeeController' , function (Container $c){
    return new EmployeeController($c->get('EmployeeRepository'));
});

$container->set('AbsenceRepository' , function (Container $c){
    return new AbsenceRepository($c->get('Connection'));
});

$container->set('AbsenceController' , function (Container $c){
    return new AbsenceController($c->get('AbsenceRepository'), $c->get('EmployeeRepository'));
});

$container->set('Router', new Router());

return $container;
