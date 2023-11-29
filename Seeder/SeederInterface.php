<?php

declare(strict_types=1);

namespace Alms\Bundle\DatabaseSeederBundle\Seeder;

use Generator;

interface SeederInterface
{
    /**
     * Returns iterable database entities.
     */
    public function run(): Generator;

    /**
     * @psalm-return positive-int
     */
    public function getPriority(): int;
}
