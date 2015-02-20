# How load the library
```json
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
```

# Register as a service in the app.php file:
```php
<?php
//...
$versionsNamespace = '\Gigigo\Migrations'; // for example (folder in your project directory)
$migrationsManagerName = 'migrations.manager'; // for example (name of the service)

$migrationsDoctrineDocuments = array(
                                array(
                                    'type' => 'annotation',
                                    'path' => array(
                                        'vendor/gigigo/mongo-migrations/src/Model',
                                    ),
                                    'namespace' => 'Gigigointernals\Mongomigrations\src\Model',
                                ));
$app['doctrine.odm.mongodb.documents'] = array_merge($app['doctrine.odm.mongodb.documents'], $migrationsDoctrineDocuments);

$app[$migrationsManagerName] = $app->share(function() use ($app) {
    return new \Gigigointernals\Mongomigrations\MigrationsManager($app['doctrine.odm.mongodb.dm'], $versionsNamespace);
});
```

# Register command, in console.php file:
```php
<?php
//...
$migrationsManagerName = 'migrations.manager'; // for example (name of the service)

$console->addCommands(array(
    new Gigigointernals\Mongomigrations\Console\MigrationsCommand($app[$migrationsManagerName])
));
```

# Usage

* Create a folder in your project directory, for example: **/src/Gigigo/Migrations**
* Adds a file version for each new version you want to apply in the database.
* The file name must begin with the letter "V" followed by the version number.
* The first file must be "V1" ( **/src/Gigigo/Migrations/V1.php** )
* The version number must be secuential.
* The next file will be "V2" and so on.

The file must be like this:

```php
<?php
namespace Gigigo\Migrations; // your namespace

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
(This example file are located in vendor/gigigo/mongo-migrations/src/Versions/V0.php)

# Command
Update database to the version 2:
```sh
# php bin/console gigigo:migrations:up --versiondb 2
```
Update database to the max version:
```sh
# php bin/console gigigo:migrations:up
```
Possible response:
```sh
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
