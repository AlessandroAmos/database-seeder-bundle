<?php

declare(strict_types=1);

namespace Alms\Bundle\DatabaseSeederBundle\Database\Strategy;

use Cycle\Database\DatabaseInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This strategy utilizes MigrationStrategy without executing migration and rolling back migration for each test
 * but performs migration prior to running the first test. It wraps the test execution in a transaction before each
 * test.
 */
class TransactionStrategy
{
    protected MigrationStrategy $migrationStrategy;

    public function __construct(
        protected DatabaseInterface $database,
        protected KernelInterface   $kernel,
        ?MigrationStrategy          $migrationStrategy = null
    )
    {
        $this->migrationStrategy = $migrationStrategy ?? new MigrationStrategy($this->kernel);
    }

    public function begin(): void
    {
        $this->migrationStrategy->migrate();

        $this->database->getDriver()->beginTransaction();
    }

    public function rollback(): void
    {
        $this->database->getDriver()->rollbackTransaction();
    }
}
