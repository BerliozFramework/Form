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

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Group;

/**
 * Class GroupHydrator.
 *
 * @package Berlioz\Form\Hydrator
 */
class GroupHydrator extends AbstractHydrator
{
    /** @var Group Group */
    private $group;

    /**
     * GroupHydrator constructor.
     *
     * @param Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @inheritdoc
     */
    public function getElement(): ElementInterface
    {
        return $this->group;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(&$mapped = null)
    {
        $subMapped = null;

        if ($this->group->getOption('disabled', false, true)) {
            return;
        }

        // Get mapped object if defined on group
        if (null !== $this->group->getMappedObject()) {
            $subMapped = $this->group->getMappedObject();
        }

        // If mapped object, and sub mapped not already defined
        if (null !== $mapped && null === $subMapped && $this->getElement()->getOption('mapped', true, true)) {
            $subMapped = $this->getSubMapped($this->getElement(), $mapped);
        }

        /** @var ElementInterface $element */
        foreach ($this->group as $element) {
            $hydrator = $this->locateHydrator($element);
            $hydrator->hydrate($subMapped);
        }
    }
}