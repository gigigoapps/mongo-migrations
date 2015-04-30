# Gigigo Mongodb Migrations for use in Silex projects
This library is used to track the queries to be made in database.

### How to load the library into composer.json
```json
{
    ...

    "repositories": [
        {
            "url": "https://github.com/gigigoapps/mongo-migrations.git",
            "type": "git"
        }
    ],
    "require": {
        ...
        "gigigoapps/mongo-migrations": "dev-master"

    }

    ...
}
```

Run:
```sh
/# composer update gigigoapps/mongo-migrations
```

### Register as a service in the app.php file:
```php
<?php

//...

$versionsPath = __DIR__ . '/../src/Gigigo/Migrations'; // for example (folder in your project directory)
$versionsNamespace = '\Gigigo\Migrations'; // for example (namespace in your project directory)
$migrationsManagerName = 'migrations.manager'; // for example (name of the service). The same in console.php

$migrationsDoctrineDocuments = array(
    array(
        'type' => 'annotation',
        'path' => array(
            'vendor/gigigoapps/mongo-migrations/src/Model',
        ),
        'namespace' => 'Gigigointernals\Mongomigrations\Model',
    ));
$app['doctrine.odm.mongodb.documents'] = array_merge($app['doctrine.odm.mongodb.documents'], $migrationsDoctrineDocuments);

$app[$migrationsManagerName] = $app->share(function() use ($app, $versionsPath, $versionsNamespace) {
    return new \Gigigointernals\Mongomigrations\MigrationsManager($app['doctrine.odm.mongodb.dm'], $versionsPath, $versionsNamespace);
});

//...
```

NOTE: MongoDBODMServiceProvider must be registered before => at least $app['doctrine.odm.mongodb.documents'] must be filled previously.


### Register command, in console.php file:
```php
<?php

//...

$migrationsManagerName = 'migrations.manager'; // for example (name of the service). The same in app.php

$console->addCommands(array(
    new Gigigointernals\Mongomigrations\Console\MigrationsCommand($app[$migrationsManagerName])
));

//...

```

### Usage

* Create a folder in your project directory, for example: **/src/Gigigo/Migrations** (set this folder into app.php like "$versionsPath")
* Adds a file version for each new version you want to apply in the database.
* The file name must begin with the letter "V" followed by the version number.
* The first file must be "V1" ( **/src/Gigigo/Migrations/V1.php** )
* The version number must be secuential.
* The next file will be "V2" and so on.

The file must be like this:

```php
<?php

namespace Gigigo\Migrations; // your namespace (set this namespace into app.php like "$versionsNamespace")

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
        $this->db->createQueryBuilder('namespace\documentname')
            ->update()
            ->multiple(true)
            ->field('active')->set(true)
            ->getQuery()
            ->execute()
        ;
    }
    
    public function down()
    {
        $this->db->createQueryBuilder('namespace\documentname')
            ->update()
            ->multiple(true)
            ->unsetField('active')
            ->getQuery()
            ->execute()
        ;
    }
}
```
(This example file is located in vendor/gigigoapps/mongo-migrations/src/Versions/V0.php)

### Command examples
Update database to the version 1:
```sh
/# php bin/console gigigo:migrations:up --versiondb 1
```

Update database to the max version:
```sh
/# php bin/console gigigo:migrations:up
```

Possible response:
```sh
/# php bin/console gigigo:migrations:up
Start update database...
[Current version: 3]
[Max version: 5]
-----------------------

Update database to the max version: 5
Update database since version: 3

Running version 4...
- Set field "foo" to true in Bar collection
Done.

Running version 5...
- Unset field "bar" from Foo collection
Done.

-----------------------
End update database in 0.065269947052002 seconds with 9.6119613647461 Mb.
```

### ToDo
- List versions and show current version
- Update to previous version
