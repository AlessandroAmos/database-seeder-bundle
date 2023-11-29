<?php

declare(strict_types=1);

namespace Alms\Bundle\DatabaseSeederBundle\Database\Traits;

use Alms\Bundle\DatabaseSeederBundle\Database\Cleaner;
use Alms\Bundle\DatabaseSeederBundle\Database\Strategy\RefreshStrategy;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Migrations\Config\MigrationConfig;
use Spiral\DatabaseSeeder\Attribute\RefreshDatabase as RefreshDatabaseAttribute;

trait RefreshDatabase
{
    private ?RefreshStrategy $refreshStrategy = null;

    /**
     * Refresh database after each test.
     */
    public function refreshDatabase(): void
    {
        $this->beforeRefreshingDatabase();

        $this->getRefreshStrategy()->refresh();

        $this->afterRefreshingDatabase();
    }

    protected function tearDownRefreshDatabase(string $database = null, array $except = []): void
    {

        $this->getRefreshStrategy()->setDatabase($database);
        $this->getRefreshStrategy()->setExcept($except);

        $this->refreshDatabase();
    }

    protected function getRefreshStrategy(): RefreshStrategy
    {
        if ($this->refreshStrategy === null) {
            $this->refreshStrategy = new RefreshStrategy(
                cleaner: new Cleaner(self::getContainer()->get(DatabaseProviderInterface::class)),
            );
        }

        return $this->refreshStrategy;
    }

    /**
     * Perform any work that should take place before the database has started refreshing.
     */
    protected function beforeRefreshingDatabase(): void
    {
        // ...
    }

    /**
     * Perform any work that should take place once the database has finished refreshing.
     */
    protected function afterRefreshingDatabase(): void
    {
        // ...
    }
}
