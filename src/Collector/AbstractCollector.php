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

namespace Berlioz\Form\Collector;

use Berlioz\Form\Collection;
use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\CollectorException;
use Berlioz\Form\Group;
use Berlioz\Form\Type\TypeInterface;
use Exception;

/**
 * Class AbstractCollector.
 *
 * @package Berlioz\Form\Collector
 */
abstract class AbstractCollector implements CollectorInterface
{

    /**
     * Get mapped sub object.
     *
     * @param ElementInterface $element Form element
     * @param object $mapped
     *
     * @return mixed
     * @throws CollectorException
     */
    protected function getSubMapped(ElementInterface $element, object $mapped)
    {
        if (is_null($element->getName())) {
            return $mapped;
        }

        try {
            $exists = false;
            $value = b_get_property_value($mapped, $element->getName(), $exists);

            // Object found
            if ($exists && !is_null($value)) {
                return $value;
            }

            return null;
        } catch (Exception $e) {
            throw new CollectorException(
                sprintf(
                    'Unable to find getter method of "%s" property on object "%s"',
                    $element->getName(),
                    get_class($mapped)
                ), 0, $e
            );
        }
    }

    /**
     * Locate collector.
     *
     * @param ElementInterface $element
     *
     * @return CollectorInterface
     * @throws CollectorException
     */
    protected function locateCollector(ElementInterface $element): CollectorInterface
    {
        if ($element instanceof TypeInterface) {
            return new TypeCollector($element);
        }

        if ($element instanceof Group) {
            return new GroupCollector($element);
        }

        if ($element instanceof Collection) {
            return new CollectionCollector($element);
        }

        throw new CollectorException(sprintf('Hydrator not found for "%s"', get_class($element)));
    }
}