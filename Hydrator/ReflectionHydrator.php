<?php

namespace Alms\Bundle\DatabaseSeederBundle\Hydrator;

class ReflectionHydrator extends \Laminas\Hydrator\ReflectionHydrator
{

    /**
     * Hydrate $object with the provided $data.
     *
     * {@inheritDoc}
     */
    public function hydrate(array $data, object $object): object
    {
        $reflProperties = self::getReflProperties($object);
        foreach ($data as $key => $value) {
            $name = $this->hydrateName($key, $data);
            if (isset($reflProperties[$name])) {

                if ($reflProperties[$name]->isReadOnly()) {
                    continue;
                }

                $reflProperties[$name]->setValue($object, $this->hydrateValue($name, $value, $data));
            }
        }
        return $object;
    }
}