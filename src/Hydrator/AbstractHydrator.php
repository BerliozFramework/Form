<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2019 Ronan GIRON
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
use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Class AbstractHydrator.
 *
 * @package Berlioz\Form\Hydrator
 */
abstract class AbstractHydrator implements HydratorInterface
{
    /**
     * Locate hydrator.
     *
     * @param ElementInterface $element
     *
     * @return HydratorInterface
     * @throws HydratorException
     */
    protected function locateHydrator(ElementInterface $element): HydratorInterface
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

        throw new HydratorException(sprintf('Hydrator not found for "%s"', get_class($element)));
    }

    /**
     * Get mapped sub object.
     *
     * @param ElementInterface $element Form element
     * @param object $mapped
     * @param bool $new New object?
     *
     * @return mixed
     * @throws HydratorException
     */
    protected function getSubMapped(ElementInterface $element, object $mapped, &$new = false)
    {
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
                        get_class($mapped)
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
                if (!b_set_property_value($mapped, $element->getName(), $subMapped)) {
                    throw new HydratorException(
                        sprintf(
                            'Unable to find setter method of "%s" property on object "%s"',
                            $element->getName(),
                            get_class($mapped)
                        ), 0
                    );
                }

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
                    get_class($mapped)
                ), 0, $e
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
            return (new ReflectionClass($dataType))->newInstance();
        } catch (ReflectionException $e) {
            throw new HydratorException(
                sprintf('Unable to create object of type "%s" to hydrate the mapped object', $dataType), 0, $e
            );
        }
    }
}