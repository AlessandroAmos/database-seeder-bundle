<?php

namespace Alms\Bundle\DatabaseSeederBundle;

use Alms\Bundle\DatabaseSeederBundle\Command\SeedCommand;
use Alms\Bundle\DatabaseSeederBundle\Database\Cleaner;
use Alms\Bundle\DatabaseSeederBundle\Factory\AbstractFactory;
use Alms\Bundle\DatabaseSeederBundle\Seeder\Executor;
use Alms\Bundle\DatabaseSeederBundle\Seeder\SeederInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class DatabaseSeederBundle extends AbstractBundle
{
    public function boot(): void
    {
        AbstractFactory::setContainer($this->container);
    }

    public function build(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(SeederInterface::class)
            ->addTag('cycle.seeder.seeder');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        $services->set('cycle.seeder.executor', Executor::class)
            ->args([
                service('cycle.orm.entity_manager'),
                service('cycle.orm.orm'),
            ]);

        $services->set('cycle.seeder.cleaner', Cleaner::class)
            ->args([
                service('cycle.dbal.database_manager'),
            ]);

        $services->set('cycle.seeder.seed.command', SeedCommand::class)
            ->args([
                service('cycle.seeder.executor'),
                service('cycle.dbal.database'),
                service('cycle.seeder.cleaner'),
                tagged_iterator('cycle.seeder.seeder'),
                param('kernel.environment'),
            ])
            ->tag('console.command');
    }
}