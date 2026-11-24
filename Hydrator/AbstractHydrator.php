<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2021 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Form\Hydrator;

use Berlioz\Form\Collection;
use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\HydratorException;
use Berlioz\Form\Group;
use Berlioz\Form\Type\TypeInterface;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;

abstract class AbstractHydrator implements HydratorInterface
{
    /**
     * @inheritDoc
     */
    public function hydrate(mixed &$mapped = null): void
    {
        $queue = new HydrationQueue();
        $this->plan($mapped, $queue);
        $queue->commit();
    }

    /**
     * Plan hydration.
     *
     * Runs every conversion and queues every write on mapped data, without modifying it.
     * A failure during planning leaves mapped data untouched.
     *
     * @param mixed $mapped
     * @param HydrationQueue $queue
     *
     * @return void
     * @throws HydratorException
     */
    abstract public function plan(mixed $mapped, HydrationQueue $queue): void;

    /**
     * Locate hydrator.
     *
     * @param ElementInterface $element
     *
     * @return AbstractHydrator
     * @throws HydratorException
     */
    protected function locateHydrator(ElementInterface $element): AbstractHydrator
    {
        if ($element instanceof TypeInterface) {
            return new TypeHydrator($element);
        }

        if ($element instanceof Group) {
            return new GroupHydrator($element);
        }

        if ($element instanceof Collection) {
            return new CollectionHydrator($element);
        }

        throw new HydratorException(sprintf('Hydrator not found for "%s"', $element::class));
    }

    /**
     * Get mapped sub object.
     *
     * @param ElementInterface $element Form element
     * @param object $mapped
     * @param bool $new New object?
     * @param HydrationQueue|null $queue Queue of planned writes, created object is attached immediately if null
     *
     * @return mixed
     * @throws HydratorException
     */
    protected function getSubMapped(
        ElementInterface $element,
        object $mapped,
        bool &$new = false,
        ?HydrationQueue $queue = null,
    ): mixed {
        if (is_null($element->getName())) {
            return $mapped;
        }

        try {
            $exists = false;
            $value = b_get_property_value($mapped, $element->getName(), $exists);

            if (!$exists) {
                throw new HydratorException(
                    sprintf(
                        'Unable to find getter method of "%s" property in mapped object "%s"',
                        $element->getName(),
                        $mapped::class
                    )
                );
            }

            // Object found
            if (!is_null($value)) {
                return $value;
            }

            // Create object
            if (!is_null($subMapped = $this->createObject($element))) {
                $new = true;

                if (null === $queue) {
                    $this->setProperty($mapped, $element->getName(), $subMapped);

                    return $subMapped;
                }

                $name = $element->getName();
                $queue->add(fn() => $this->setProperty($mapped, $name, $subMapped));

                return $subMapped;
            }

            return null;
        } catch (HydratorException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new HydratorException(
                sprintf(
                    'Unable to find setter method of "%s" property on object "%s"',
                    $element->getName(),
                    $mapped::class
                ), 0, $e
            );
        }
    }

    /**
     * Set property value on mapped object.
     *
     * @param object $mapped
     * @param string $property
     * @param mixed $value
     *
     * @return void
     * @throws HydratorException
     */
    protected function setProperty(object $mapped, string $property, mixed $value): void
    {
        try {
            $written = b_set_property_value($mapped, $property, $value);
        } catch (Exception $e) {
            throw new HydratorException(
                sprintf('Unable to find setter method of "%s" property on object "%s"', $property, $mapped::class),
                0,
                $e
            );
        }

        if (!$written) {
            throw new HydratorException(
                sprintf('Unable to find setter method of "%s" property on object "%s"', $property, $mapped::class)
            );
        }
    }

    /**
     * Create object.
     *
     * @param ElementInterface|null $element
     *
     * @return object|null
     * @throws HydratorException
     */
    protected function createObject(?ElementInterface $element = null): ?object
    {
        if (is_null($element)) {
            $element = $this->getElement();
        }

        if (is_null($dataType = $element->getOption('data_type'))) {
            return null;
        }

        try {
            if ($dataType instanceof Closure) {
                return $dataType($element);
            }

            return (new ReflectionClass($dataType))->newInstance();
        } catch (ReflectionException $e) {
            throw new HydratorException(
                sprintf('Unable to create object of type "%s" to hydrate the mapped object', $dataType), 0, $e
            );
        }
    }
}
