<?php

declare(strict_types=1);

namespace Alms\Bundle\DatabaseSeederBundle\Database\Traits;

use Alms\Bundle\DatabaseSeederBundle\Database\EntityAssertion;
use Alms\Bundle\DatabaseSeederBundle\Database\TableAssertion;
use Cycle\Database\DatabaseInterface;
use Cycle\ORM\ORMInterface;
use function is_object;

trait DatabaseAsserts
{
    /**
     * Build entity assertion.
     *
     * @param class-string|object $entity
     */
    public function assertEntity(string|object $entity): EntityAssertion
    {
        if (is_object($entity)) {
            $entity = $entity::class;
        }

        return new EntityAssertion($entity, self::getContainer()->get(ORMInterface::class));
    }

    /**
     * Build table assertion.
     *
     * @param non-empty-string $table
     */
    public function assertTable(string $table): TableAssertion
    {
        return new TableAssertion($table, self::getContainer()->get(DatabaseInterface::class));
    }
}
