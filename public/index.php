<?php

use Phalcon\Loader;
use Phalcon\Tag;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

use Phalcon\Mvc\Router\Annotations as RouterAnnotations;

use Services\Parking;

try {
    
    // Register an autoloader
    $loader = new Loader();
    $loader->registerDirs(
        array(
            '../app/controllers/',
            '../app/models/'
        )
    )->register();

    // Register namespaces
    $loader->registerNamespaces([
        'Models'   => '../app/models',
        'Services' => '../app/services',
        'Library'  => '../app/library'
    ]);

    // Create a DI
    $di = new FactoryDefault();

    // Set the database service
    // TODO: remove credentials
    $di['db'] = function() {
        return new DbAdapter(array(
            "host"     => "localhost",
            "username" => "soapbox",
            "password" => "soapbox",
            "dbname"   => "parking"
        ));
    };

    // Setting up the view component
    $di['view'] = function() {
        $view = new View();
        $view->setViewsDir('../app/views/');
        return $view;
    };

    // Set API routes
    $di['router'] = function() {
        $router = new RouterAnnotations(false);
        $router->addResource('Parking', '/api/parking');

        return $router;
    };

    // Initialize Parking service
    $di->setShared('parking', function() {
        return new Parking();
    });

    // Handle the request
    $application = new Application($di);

    echo $application->handle()->getContent();

} catch (Exception $e) {
    echo "Exception: ", $e->getMessage();
}
