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

use ArrayAccess;
use Berlioz\Form\Collection;
use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\HydratorException;
use Exception;

class CollectionHydrator extends AbstractHydrator
{
    /**
     * CollectionHydrator constructor.
     *
     * @param Collection $collection
     */
    public function __construct(private readonly Collection $collection)
    {
    }

    /**
     * @inheritDoc
     */
    public function getElement(): Collection
    {
        return $this->collection;
    }

    /**
     * @inheritDoc
     */
    public function plan(mixed $mapped, HydrationQueue $queue): void
    {
        if (!$this->getElement()->getOption('mapped', true, true)) {
            return;
        }

        if ($this->collection->getOption('disabled', false, true)) {
            return;
        }

        if ($this->collection->getOption('readonly', false, true)) {
            return;
        }

        if (false === $this->collection->isSubmitted()) {
            return;
        }

        $subMapped = $this->getSubMapped($this->getElement(), $mapped, queue: $queue) ?? [];
        $isArray = is_array($subMapped);
        $isArrayAccess = $subMapped instanceof ArrayAccess;

        if (!$isArray && !$isArrayAccess) {
            throw new HydratorException('Collection field must be an array or an \ArrayAccess object');
        }

        // Prototype
        $prototype = $this->collection->getPrototype();

        // Get keys submit by form element
        $submittedKeys = array_keys($this->collection->getValue());

        // List submapped's key who need to be removed
        $removeKey = [];
        foreach ($subMapped as $key => $value) {
            if (!in_array($key, $submittedKeys)) {
                $removeKey[] = $key;
            }
        }

        // Delete old submapped values
        if ($isArray) {
            foreach ($removeKey as $key) {
                unset($subMapped[$key]);
            }
        }
        if ($isArrayAccess && count($removeKey) > 0) {
            $queue->add(function () use ($subMapped, $removeKey): void {
                foreach ($removeKey as $key) {
                    unset($subMapped[$key]);
                }
            });
        }

        /** @var ElementInterface $element */
        foreach ($this->collection as $key => $element) {
            if (!in_array($key, $submittedKeys)) {
                continue;
            }

            $obj = $this->createObject($prototype);

            if (null !== $obj) {
                if (isset($subMapped[$key])) {
                    $obj = $subMapped[$key];
                } else {
                    $this->setItem($subMapped, $key, $obj, $queue);
                }

                $hydrator = $this->locateHydrator($element);
                if (!$hydrator instanceof TypeHydrator) {
                    $hydrator->plan($obj, $queue);
                }
                continue;
            }

            $this->setItem($subMapped, $key, $element->getFinalValue(), $queue);
        }

        // Is array? Need to set array, because no reference on arrays.
        if ($isArray) {
            $propertyName = $this->getElement()->getName();

            try {
                $subMapped = $this->collection->getTransformer()->fromForm($subMapped, $this->collection);
            } catch (Exception $e) {
                throw new HydratorException(
                    sprintf('Unable to transform value of collection "%s"', $propertyName),
                    0,
                    $e
                );
            }

            $queue->add(function () use ($mapped, $propertyName, $subMapped): void {
                try {
                    b_set_property_value($mapped, $propertyName, $subMapped);
                } catch (Exception $e) {
                    throw new HydratorException(
                        sprintf(
                            'Unable to find property setter of "%s" on object "%s"',
                            $propertyName,
                            $mapped::class
                        ),
                        0,
                        $e
                    );
                }
            });
        }
    }

    /**
     * Set an item of collection.
     *
     * Arrays are local copies, so they are modified directly and written back at the end of planning;
     * \ArrayAccess objects are shared, so the write is queued.
     *
     * @param array|ArrayAccess $subMapped
     * @param int|string $key
     * @param mixed $value
     * @param HydrationQueue $queue
     *
     * @return void
     */
    private function setItem(array|ArrayAccess &$subMapped, int|string $key, mixed $value, HydrationQueue $queue): void
    {
        if (is_array($subMapped)) {
            $subMapped[$key] = $value;
            return;
        }

        $collection = $subMapped;
        $queue->add(function () use ($collection, $key, $value): void {
            $collection[$key] = $value;
        });
    }
}
