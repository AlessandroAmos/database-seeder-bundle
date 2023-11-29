<?php

namespace Alms\Bundle\DatabaseSeederBundle\Database\Traits;

use Alms\Bundle\DatabaseSeederBundle\Logger\StdoutLogger;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Driver\DriverInterface;
use Psr\Log\LoggerInterface;
use ReflectionProperty;

trait ShowQueries
{
    private ?LoggerInterface $originalLogger = null;

    public function showDatabaseQueries(): void
    {
        $driver = $this->getDriver();

        if ($this->originalLogger === null) {
            $this->originalLogger = (new ReflectionProperty($driver, 'logger'))->getValue($driver);
        }

        $driver->setLogger(new StdoutLogger());
    }

    protected function tearDownShowQueries(): void
    {
        $this->restoreLogger();
    }

    private function restoreLogger(): void
    {
        if ($this->originalLogger === null) {
            return;
        }

        $this->getDriver()->setLogger($this->originalLogger);

        $this->originalLogger = null;
    }

    private function getDriver(): DriverInterface
    {
        /**
         * @var DatabaseInterface $database
         */
        $database = self::getContainer()->get(DatabaseInterface::class);

        return $database->getDriver();
    }
}