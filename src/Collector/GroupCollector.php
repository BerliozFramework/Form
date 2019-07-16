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

namespace Berlioz\Form\Collector;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Group;

/**
 * Class GroupCollector.
 *
 * @package Berlioz\Form\Collector
 */
class GroupCollector extends AbstractCollector
{
    /** @var  \Berlioz\Form\Group Group */
    private $group;

    /**
     * GroupCollector constructor.
     *
     * @param \Berlioz\Form\Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @inheritdoc
     * @return \Berlioz\Form\Group
     */
    public function getElement(): ElementInterface
    {
        return $this->group;
    }

    /**
     * @inheritdoc
     */
    public function collect($mapped)
    {
        $collected = [];
        $subMapped = $this->getSubMapped($this->getElement(), $mapped);

        /** @var \Berlioz\Form\Element\ElementInterface $element */
        foreach ($this->group as $element) {
            if ($element->getOption('mapped', false, true)) {
                $collected[$element->getName()] = null;

                if (!is_null($subMapped)) {
                    $collector = $this->locateCollector($element);
                    $collected[$element->getName()] = $collector->collect($subMapped);
                }
            }
        }

        return $collected;
    }
}