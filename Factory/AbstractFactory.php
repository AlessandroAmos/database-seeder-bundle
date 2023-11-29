<?php

namespace Alms\Bundle\DatabaseSeederBundle\Factory;

use Alms\Bundle\DatabaseSeederBundle\Factory\Exception\FactoryException;
use Alms\Bundle\DatabaseSeederBundle\Hydrator\ReflectionHydrator;
use Butschster\EntityFaker\EntityFactory\ClosureStrategy;
use Butschster\EntityFaker\EntityFactory\InstanceWithoutConstructorStrategy;
use Butschster\EntityFaker\Factory;
use Butschster\EntityFaker\LaminasEntityFactory;
use Closure;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * @template TEntity of object
 *
 * @implements FactoryInterface<TEntity>
 *
 * @property-read $data
 */
abstract class AbstractFactory implements FactoryInterface
{
    /** @internal */
    private Factory $entityFactory;
    /** @psalm-var positive-int */
    private int $amount = 1;
    /** @var array<Closure|callable> */
    private array $afterCreate = [];
    /** @var array<Closure|callable> */
    private array $afterMake = [];
    protected Generator $faker;
    /** @var array<Closure> */
    private array $states = [];
    /** @var array<Closure> */
    private array $entityStates = [];

    protected static ?ContainerInterface $container = null;

    private function __construct(
        private readonly array $replaces = [],
    )
    {
        $this->faker = FakerFactory::create();

        $this->entityFactory = new Factory(
            new LaminasEntityFactory(
                new ReflectionHydrator(),
                new InstanceWithoutConstructorStrategy(),
            ),
            $this->faker,
        );
    }

    protected static function getContainer(): ?ContainerInterface
    {
        return self::$container;
    }

    /**
     * @throws RuntimeException
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * In this method you should describe how to create an entity with the given definition.
     *
     * @return TEntity
     */
    abstract public function makeEntity(array $definition): object;

    public static function new(array $replace = []): static
    {
        return new static($replace);
    }

    public function times(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function state(Closure $state): self
    {
        $this->states[] = $state;

        return $this;
    }

    public function entityState(Closure $state): self
    {
        $this->entityStates[] = $state;

        return $this;
    }

    public function afterCreate(callable $afterCreate): self
    {
        $this->afterCreate[] = $afterCreate;

        return $this;
    }

    public function afterMake(callable $afterMake): self
    {
        $this->afterMake[] = $afterMake;

        return $this;
    }

    public function create(): array
    {
        $entities = $this->object(fn() => $this->definition());
        if (!\is_array($entities)) {
            $entities = [$entities];
        }

        $this->storeEntities($entities);

        $this->callAfterCreating($entities);

        return $entities;
    }

    public function createOne(): object
    {
        $entity = $this->object(fn() => $this->definition());
        if (\is_array($entity)) {
            $entity = \array_shift($entity);
        }

        $this->storeEntities([$entity]);

        $this->callAfterCreating([$entity]);

        return $entity;
    }

    public function make(): array
    {
        $entities = $this->object(fn() => $this->definition());
        if (!\is_array($entities)) {
            $entities = [$entities];
        }

        return $entities;
    }

    public function makeOne(): object
    {
        $entity = $this->object(fn() => $this->definition());
        if (\is_array($entity)) {
            $entity = \array_shift($entity);
        }

        return $entity;
    }

    public function raw(Closure $definition): array
    {
        $this->entityFactory->define($this->entity(), $definition);

        $data = $this->entityFactory->of($this->entity())->times($this->amount)->raw($this->replaces);

        return \array_is_list($data) ? $data[0] : $data;
    }

    public function __get(string $name): array
    {
        return match ($name) {
            'data' => $this->raw(fn() => $this->definition()),
            default => throw new FactoryException('Undefined magic property.')
        };
    }

    private function storeEntities(array $entities): void
    {
        $container = self::getContainer();

        if ($container === null) {
            throw new RuntimeException('Container is not set.');
        }

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        foreach ($entities as $entity) {
            $em->persist($entity);
        }

        $em->run();

        $container->get(ORMInterface::class)->getHeap()->clean();
    }

    /**
     * @return TEntity|array
     * @internal
     *
     */
    private function object(Closure $definition): object|array
    {
        $this->entityFactory
            ->creationStrategy(
                $this->entity(),
                new ClosureStrategy(fn(string $class, array $data) => $this->makeEntity($data)),
            )
            ->define($this->entity(), $definition)
            ->states($this->entity(), $this->states);

        foreach ($this->afterMake as $afterMakeCallable) {
            $this->entityFactory->afterMaking($this->entity(), $afterMakeCallable);
        }

        $result = $this->entityFactory->of($this->entity())->times($this->amount)->make($this->replaces);

        if (\is_array($result)) {
            return \array_map(function (object $entity) {
                return $this->applyEntityState($entity);
            }, $result);
        }

        return $this->applyEntityState($result);
    }

    /**
     * @return TEntity
     * @internal
     *
     */
    private function applyEntityState(object $entity): object
    {
        foreach ($this->entityStates as $state) {
            $entity = $state($entity);
        }

        return $entity;
    }

    /** @internal */
    private function callAfterCreating(array $entities): void
    {
        foreach ($entities as $entity) {
            \array_map(static fn(callable $callable) => $callable($entity), $this->afterCreate);
        }
    }
}