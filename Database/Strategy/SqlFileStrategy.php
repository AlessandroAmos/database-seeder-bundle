<?php

declare(strict_types=1);

namespace Alms\Bundle\DatabaseSeederBundle\Database\Strategy;

use Alms\Bundle\DatabaseSeederBundle\Database\DatabaseState;
use Alms\Bundle\DatabaseSeederBundle\Database\Exception\DatabaseException;
use Cycle\Database\DatabaseProviderInterface;
use function assert;
use function file_get_contents;
use function is_string;

class SqlFileStrategy
{
    public function __construct(
        protected readonly string                    $preparePath,
        protected readonly DatabaseProviderInterface $provider,
        protected ?string                            $dropPath = null,
        protected ?string                            $database = null,
    )
    {
    }

    public function execute(): void
    {
        if (DatabaseState::$migrated) {
            return;
        }

        $sql = file_get_contents($this->preparePath);

        if (!is_string($sql)) {
            throw new DatabaseException('Could not read SQL file.');
        }
        assert(!empty($sql));

        $database = $this->provider->database($this->database);

        $database->getDriver()->query($sql)->close();

        DatabaseState::$migrated = true;
    }

    public function drop(): void
    {
        if (empty($this->dropPath)) {
            return;
        }

        $sql = file_get_contents($this->dropPath);

        if (!is_string($sql)) {
            throw new DatabaseException('Could not read SQL file.');
        }
        assert(!empty($sql));

        $database = $this->provider->database($this->database);

        $database->getDriver()->query($sql)->close();

        DatabaseState::$migrated = false;
    }
}
