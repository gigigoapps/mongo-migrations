
How load the library

{
    "repositories": [
        {
            "url": "https://arranzsancho@bitbucket.org/arranzsancho/mongo-migrations.git",
            "type": "git"
        }
    ],
    "require": {
        "gigigo/mongo-migrations": "dev-master"

    }
}


Register as a service in the app.php file:

$migrationsDoctrineDocuments = array(
                                array(
                                    'type' => 'annotation',
                                    'path' => array(
                                        'vendor/gigigo/mongo-migrations/src/Model',
                                    ),
                                    'namespace' => 'Gigigointernals\Mongomigrations\src\Model',
                                ));
$app['doctrine.odm.mongodb.documents'] = array_merge($app['doctrine.odm.mongodb.documents'], $migrationsDoctrineDocuments);
$versionsNamespace = '\Gigigo\Migrations'; // for example (folder in your project directory)
$migrationsManagerName = 'migrations.manager'; // for example (name of the service)
$app[$migrationsManagerName] = $app->share(function() use ($app) {
    return new \Gigigointernals\Mongomigrations\MigrationsManager($app['doctrine.odm.mongodb.dm'], $versionsNamespace);
});


Register command, in console.php file:

$migrationsManagerName = 'migrations.manager'; // for example (name of the service)
$console->addCommands(array(
    new Gigigointernals\Mongomigrations\Console\MigrationsCommand($app[$migrationsManagerName])
));


Usage

Create a folder in your project directory, for example: /src/Gigigo/Migrations

Adds a file version for each new version you want to apply in the database.
The file name must begin with the letter "V" followed by the version number.
The first file must be "V1".
The version number must be secuential. The next file will be "V2".

The file must be like this:
(This example file are located in vendor/gigigo/mongo-migrations/src/Versions/V0.php)

<?php
namespace Gigigo\Migrations;

use Gigigointernals\Mongomigrations\VersionBase as VersionBase;

/**
 * Example version class
 */
class V0 extends VersionBase
{
    public function getDescription()
    {
        return 'This is the description of the queries that will be executed in the method up()';
    }
    
    public function up()
    {
        $this->db->createQueryBuilder('namespace\classname')
            ->update()
            ->multiple(true)
            ->field('active')->set(true)
            ->getQuery()
            ->execute()
        ;
    }
    
    public function down()
    {
        $this->db->createQueryBuilder('namespace\classname')
            ->update()
            ->multiple(true)
            ->unsetField('active')
            ->getQuery()
            ->execute()
        ;
    }
}

This file are located in vendor/gigigo/mongo-migrations/src/Versions/V0.php

The name


