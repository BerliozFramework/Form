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

namespace Berlioz\Form\Collector;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Group;

class GroupCollector extends AbstractCollector
{
    /**
     * GroupCollector constructor.
     *
     * @param Group $group
     */
    public function __construct(private Group $group)
    {
    }

    /**
     * @inheritDoc
     * @return Group
     */
    public function getElement(): ElementInterface
    {
        return $this->group;
    }

    /**
     * @inheritDoc
     */
    public function collect(mixed $mapped = null): array
    {
        $collected = [];
        $subMapped = null;

        // Get mapped object if defined on group
        if (null !== $this->group->getMappedObject()) {
            $subMapped = $this->group->getMappedObject();
        }

        // If mapped object, and sub mapped not already defined
        if (null !== $mapped && null === $subMapped) {
            $subMapped = $this->getSubMapped($this->getElement(), $mapped);
        }

        /** @var ElementInterface $element */
        foreach ($this->group as $element) {
            if (!$element->getOption('mapped', false, true)) {
                continue;
            }

            $collector = $this->locateCollector($element);
            $collectedValue = $collector->collect($subMapped);

            if (null !== $collectedValue) {
                $collected[$element->getName()] = $collectedValue;
                continue;
            }

            $collected[$element->getName()] = null;
        }

        return $collected;
    }
}