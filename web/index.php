<?php

use Symfony\Component\HttpFoundation\Request;


$container = include __DIR__ . '/../app/bootstrap.php';

$container->get('Router')->add('/^\/employee$/', $container->get('EmployeeController'), 'list');
$container->get('Router')->add('/^\/employee\/save$/', $container->get('EmployeeController'), 'add');
$container->get('Router')->add('/^\/employee\/save\/([a-zA-Z0-9-]+)$/', $container->get('EmployeeController'), 'update');
$container->get('Router')->add('/^\/employee\/delete\/([a-zA-Z0-9-]+)$/', $container->get('EmployeeController'), 'delete');
$container->get('Router')->add('/^\/employee\/([a-zA-Z0-9-]+)$/', $container->get('EmployeeController'), 'get');
$container->get('Router')->add('/^\/absence$/', $container->get('AbsenceController'), 'list');
$container->get('Router')->add('/^\/absence\/save$/', $container->get('AbsenceController'), 'add');
$container->get('Router')->add('/^\/absence\/delete\/([a-zA-Z0-9-]+)$/', $container->get('AbsenceController'), 'delete');
$container->get('Router')->add('/^\/absence\/([a-zA-Z0-9-]+)$/', $container->get('AbsenceController'), 'get');

$response = $container->get('Router')->execute(Request::createFromGlobals());
$response->send();
