## Database test setUp

### 1. Transaction Strategy

It sets up your database for testing when you run your first test. Then, for each test, it does everything inside a
transaction – a kind of protected space. After the test is done, it undoes everything (rolls back) that happened in the
transaction. Imagine you're testing adding a new user to your database. The Transaction Strategy would add this user
inside a transaction and then, after the test, remove any trace of this new user, as if they were never added.

This is the fastest way and is great for most tests. It's especially good when you have lots of tests that don't change
the database structure but just add, change, or remove data.

**Here is an example of a test case that uses it:**

```php
declare(strict_types=1);

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Alms\Bundle\DatabaseSeederBundle\Database\Traits\{
    DatabaseAsserts, Helper, ShowQueries, Transactions
};

class DatabaseTest extends KernelTestCase
{
    use Transactions,
        Helper,
        DatabaseAsserts,
        ShowQueries;
        
        
    public function setUp() : void
    {
        $this->setUpTransactions();
    }
    
    public function tearDown() : void
    {
        $this->tearDownTransactions();
    }
    
}
```

Let's examine each trait used:

- `Transactions` trait: It manages the wrapping of each test case in a database transaction. When a test begins, it
  starts a transaction. Once the test is completed, whether it passes or fails, the transaction is rolled back. This
  ensures that each test is isolated and the database is left unchanged after the test, making the tests independent of
  each other. It also handles running database migrations before the tests, setting up the necessary database schema.
- `Helper` trait: It provides a set of helper methods for interacting with the database. It includes utility functions
  that simplify various database operations, making it easier to perform common tasks in your tests without writing
  repetitive code.
- `DatabaseAsserts` trait: It offers a range of assertions you can use to validate the state of your database,
  such as checking if a record exists, verifying the count of records, etc. It's crucial for ensuring that your database
  operations produce the expected results.
- `ShowQueries` trait: This is particularly useful for debugging. When you enable this trait, it logs all the SQL
  queries executed during the test to the terminal. This can help you understand what's happening in your database
  during tests and troubleshoot any issues that arise.

### 2. Migration Strategy

This method sets up your database using migrations (steps to prepare your database) before each test. After the test, it
rollbacks these migrations. t's slower than the Transaction Strategy but useful if you need to test changes in the
database structure itself.

**Example:** If you're testing creating a new table in your database, the Migration Strategy would create this table for
the test and then remove it afterward.

**Here is an example of a test case that uses it:**

```php
declare(strict_types=1);

namespace Tests;

use Alms\Bundle\DatabaseSeederBundle\Database\Traits\{
    DatabaseAsserts, DatabaseMigrations, Helper, ShowQueries
};

class DatabaseTest extends KernelTestCase
{
    use DatabaseMigrations,
        Helper,
        DatabaseAsserts,
        ShowQueries;
        
        
    public function setUp() : void
    {
        $this->setUpDatabaseMigrations();
    }
    
    public function tearDown() : void
    {
        $this->tearDownDatabaseMigrations();
    }
    
}
```

- `DatabaseMigrations` trait: It manages the running of database migrations before each test and rolling them back
  afterward. This ensures that each test is isolated and the database is left unchanged after the test, making the tests
  independent of each other. It also handles running database migrations before the tests, setting up the necessary
  database schema.

### 3. Refresh Strategy

This strategy cleans your database after each test using `Alms\Bundle\DatabaseSeederBundle\Database\Cleaner`. It's
useful if your
database already has the structure you need, and you're just testing data changes. It's slower than the Transaction
Strategy.

**Here is an example of a test case that uses it:**

```php
declare(strict_types=1);

namespace Tests;

use Alms\Bundle\DatabaseSeederBundle\Database\Traits\{
    DatabaseAsserts, Helper, RefreshDatabase, ShowQueries
};

class DatabaseTest extends KernelTestCase
{
    use RefreshDatabase,
        Helper,
        DatabaseAsserts,
        ShowQueries;
        
   
    public function tearDown() : void
    {
        $this->tearDownRefreshDatabase();
    }
    
}
```

- `RefreshDatabase` trait: It manages the cleaning of the database tables (except `migrations` table) after each test.

### 4. SqlFile Strategy

This approach uses an SQL file to set up your database with the necessary structure and data for testing. It's handy
when you have a complex setup that can be easily loaded from a file, but it's slower than the Transaction Strategy.

**Example:** If you need a specific setup with many tables and data, the SqlFile Strategy lets you load all this from a
pre-prepared SQL file.

**Here is an example of a test case that uses it:**

```php
declare(strict_types=1);

namespace Tests;

use Alms\Bundle\DatabaseSeederBundle\Database\Traits\{
    DatabaseAsserts, Helper, DatabaseFromSQL, ShowQueries
};

class DatabaseTest extends KernelTestCase
{
    use DatabaseFromSQL,
        Helper,
        DatabaseAsserts,
        ShowQueries;
        
        
    public function setUp() : void
    {
        $this->setUpDatabaseFromSQL();
    }
    
    public function tearDown() : void
    {
        $this->tearDownDatabaseFromSQL();
    }
    
    protected function getPrepareSQLFilePath(): string
   {
       return __DIR__ . '/database/prepare.sql';
   }

    protected function getDropSQLFilePath(): string
    {
        return __DIR__ . '/database/prepare.sql';
    }
    
}
```

As you can see, there are lots of ways to set up and reset your database for testing. You can choose the one that fits
your needs best. After you set up your database, you can start defining your factories and seeders.