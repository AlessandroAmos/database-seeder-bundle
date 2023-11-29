<?php

declare(strict_types=1);

namespace Alms\Bundle\DatabaseSeederBundle\Database\Traits;

use Alms\Bundle\DatabaseSeederBundle\Database\Cleaner;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;

trait Helper
{
    private ?Cleaner $cleaner = null;

    public function getDatabaseCleaner(): Cleaner
    {
        if ($this->cleaner === null) {
            $this->cleaner = new Cleaner($this->getCurrentDatabaseProvider());
        }

        return $this->cleaner;
    }

    public function getCurrentDatabase(): DatabaseInterface
    {
        return self::getContainer()->get(DatabaseInterface::class);
    }

    public function getCurrentDatabaseDriver(): DriverInterface
    {
        return $this->getCurrentDatabase()->getDriver();
    }

    public function getCurrentDatabaseProvider(): DatabaseProviderInterface
    {
        return self::getContainer()->get(DatabaseProviderInterface::class);
    }

    public function getOrm(): ORMInterface
    {
        return self::getContainer()->get(ORMInterface::class);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }

    public function detachEntityFromIdentityMap(object $entity): void
    {
        $this->getOrm()->getHeap()->detach($entity);
    }

    public function cleanIdentityMap(): void
    {
        $this->getOrm()->getHeap()->clean();
    }

    public function getRepositoryFor(object|string $entity): RepositoryInterface
    {
        return $this->getOrm()->getRepository($entity);
    }

    public function persist(object $entity): void
    {
        $this->getEntityManager()->persist($entity)->run();
    }
}
