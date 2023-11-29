## Seeding

The package provides the ability to seed the database with test data. To do this, developers can create a Seeder class
and extend it from the `Alms\Bundle\DatabaseSeederBundle\Seeder\AbstractSeeder` class. The Seeder class should implement
the `run`
method which returns a generator with entities to store in the database.

```php
namespace Database\Seeder;

use Alms\Bundle\DatabaseSeederBundle\Seeder\SeederInterface;
use Alms\Bundle\DatabaseSeederBundle\Seeder\AbstractSeeder;

final class UserTableSeeder extends AbstractSeeder implements SeederInterface
{
    public function run(): \Generator
    {
        foreach (UserFactory::new()->times(100)->make() as $user) {
            yield $user;
        }
        
        foreach (UserFactory::new()->admin()->times(10)->make() as $user) {
            yield $user;
        }
        
        foreach (UserFactory::new()->admin()->deleted()->times(1)->make() as $user) {
            yield $user;
        }
    }
}
```

Seeders are primarily used to fill the database with test data for your stage server, providing a consistent set of data
for the developers and stakeholders to test and use.

They are especially useful when testing complex applications that have many different entities and relationships
between them. By using seeders to populate the database with test data, you can ensure that your tests are run against a
consistent and well-defined set of data, which can help to make your tests more reliable and less prone to flakiness.

### Running seeders

Use the following command to run the seeders:

```terminal
php bin/console cycle:seed
```

> **Note**
> When `cycle:seed` command is executed, it will truncate all tables in the database except the `migrations` table.
> if you don't want to truncate tables, you can use the `--append` option.


This will execute all of the seeder classes that are registered and insert the test data into the database.