<?php

declare(strict_types=1);

namespace Alms\Bundle\DatabaseSeederBundle\Seeder;

interface ExecutorInterface
{
    /**
     * Execute all seeders classes and seed database.
     *
     * @psalm-param iterable<SeederInterface> $seeders
     */
    public function execute(iterable $seeders): void;

    /**
     * The event, calling after seeding each seeder class
     */
    public function afterSeed(callable $callback): self;
}
