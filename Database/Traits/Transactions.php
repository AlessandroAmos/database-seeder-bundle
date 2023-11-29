<?php

declare(strict_types=1);

namespace Alms\Bundle\DatabaseSeederBundle\Database\Traits;

use Alms\Bundle\DatabaseSeederBundle\Database\Strategy\TransactionStrategy;
use Cycle\Database\DatabaseInterface;
use Symfony\Component\HttpKernel\KernelInterface;

trait Transactions
{
    private ?TransactionStrategy $transactionStrategy = null;


    public function beginTransaction(): void
    {
        $this->beforeBeginTransaction();

        $this->getTransactionStrategy()->begin();

        $this->afterBeginTransaction();
    }

    public function rollbackTransaction(): void
    {
        $this->beforeRollbackTransaction();

        $this->getTransactionStrategy()->rollback();

        $this->afterRollbackTransaction();
    }

    protected function setUpTransactions(): void
    {
        $this->beginTransaction();
    }

    protected function tearDownTransactions(): void
    {
        $this->rollbackTransaction();
    }

    protected function getTransactionStrategy(): TransactionStrategy
    {
        $container = self::getContainer();

        if ($this->transactionStrategy === null) {
            $this->transactionStrategy = new TransactionStrategy(
                database: $container->get(DatabaseInterface::class),
                kernel: $container->get(KernelInterface::class)
            );
        }

        return $this->transactionStrategy;
    }

    /**
     * Perform any work before the database transaction has started
     */
    protected function beforeBeginTransaction(): void
    {
        // ...
    }

    /**
     * Perform any work after the database transaction has started
     */
    protected function afterBeginTransaction(): void
    {
        // ...
    }

    /**
     * Perform any work before rolling back the transaction
     */
    protected function beforeRollbackTransaction(): void
    {
        // ...
    }

    /**
     * Perform any work after rolling back the transaction
     */
    protected function afterRollbackTransaction(): void
    {
        // ...
    }
}
