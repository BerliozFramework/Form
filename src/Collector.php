<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form;

use Berlioz\Form\Exception\CollectorException;
use Berlioz\Form\Type\AbstractType;

class Collector
{
    /**
     * Collector constructor.
     *
     * @param \Berlioz\Form\Form $form
     */
    public function __construct()
    {
    }

    /**
     * Collect data for group.
     *
     * @param \Berlioz\Form\Group $group
     * @param mixed               $mapped
     *
     * @return array
     * @throws \Berlioz\Form\Exception\CollectorException
     */
    public function collectGroup(Group $group, &$mapped): array
    {
        $groupValues = [];

        if (!$group->getOption('mapped', false, true)) {
            return $groupValues;
        }

        $subMapped = $mapped;
        $transformer = $group->getTransformer();

        if (!$group instanceof Form && !is_null($group->getName())) {
            if (is_null($subMapped = $this->collectValue($mapped, $group->getName()))) {
                throw new CollectorException(sprintf('Getter of property "%s" returns NULL in class "%s"', $group->getName(), get_class($mapped)));
            }
        }

        // Apply the group data transformer prior to collection if present
        if (!empty($transformer)) {
            $subMapped = $transformer->toForm($subMapped);
        }

        /** @var \Berlioz\Form\ElementInterface $item */
        foreach ($group as $item) {
            if (!is_null($item->getName())) {
                $value = null;
                if ($item instanceof AbstractType) {
                    $value = $this->collectField($item, $subMapped);
                } elseif ($item instanceof Group) {
                    $value = $this->collectGroup($item, $subMapped);
                } elseif ($item instanceof Collection) {
                    $value = $this->collectCollection($item, $subMapped);
                }

                if (!is_null($value)) {
                    $groupValues[$item->getName()] = $value;
                }
            } else {
                if ($item instanceof Group) {
                    $this->collectGroup($item, $mapped);
                } else {
                    throw new CollectorException('Unable to collect data on item not named');
                }
            }
        }

        return $groupValues;
    }

    /**
     * Collect data for collection.
     *
     * @param \Berlioz\Form\Collection $collection
     * @param mixed                    $mapped
     *
     * @return array
     * @throws \Berlioz\Form\Exception\CollectorException
     */
    public function collectCollection(Collection $collection, &$mapped): array
    {
        $collectionValues = [];

        if ($collection->getOption('mapped', false, true)) {
            $mappedCollection = $this->collectValue($mapped, $collection->getName());

            if (is_array($mappedCollection) || $mappedCollection instanceof \Traversable) {
                $prototype = $collection->getPrototype();

                $i = 0;
                foreach ($mappedCollection as $mappedValue) {
                    $value = null;
                    if ($prototype instanceof AbstractType) {
                        $value = $this->collectField($prototype, $mappedValue);
                    } elseif ($prototype instanceof Group) {
                        $value = $this->collectGroup($prototype, $mappedValue);
                    }

                    if (!is_null($value)) {
                        $collectionValues[$i] = $value;
                    }

                    $i++;
                }
            }
        }

        return $collectionValues;
    }

    /**
     * Collect data for field.
     *
     * @param \Berlioz\Form\Type\AbstractType $type
     * @param mixed $mapped
     *
     * @return mixed|null
     * @throws \Berlioz\Form\Exception\CollectorException
     * @throws \ReflectionException
     */
    public function collectField(AbstractType $type, &$mapped)
    {
        if ($type->getOption('mapped', false, true)) {
            if (is_null($type->getName())) {
                return $mapped;
            } else {
                return $this->collectValue($mapped, $type->getName());
            }
        }

        return null;
    }

    /**
     * Collect value from mapped.
     *
     * @param mixed  $mapped
     * @param string $property
     *
     * @return mixed
     * @throws \Berlioz\Form\Exception\CollectorException
     * @throws \ReflectionException
     */
    public function collectValue(&$mapped, string $property)
    {
        if (is_array($mapped)) {
            return $mapped[$property];
        }

        $exists = false;
        $value = b_get_property_value($mapped, $property, $exists);

        if (!$exists) {
            throw new CollectorException(sprintf('Missing getter for "%s" property in object "%s"', $property, get_class($mapped)));
        }

        return $value;
    }

    /**
     * Collect.
     *
     * @param \Berlioz\Form\Form $form
     *
     * @return array
     * @throws \Berlioz\Form\Exception\CollectorException
     * @throws \Berlioz\Form\Exception\FormException
     */
    public function collect(Form $form): array
    {
        if (!is_null($mapped = $form->getMappedObject())) {
            $formValues = $this->collectGroup($form, $mapped);

            return $formValues;
        }

        return [];
    }
}
