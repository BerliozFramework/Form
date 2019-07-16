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

namespace Berlioz\Form\Element;

use ArrayIterator;
use Berlioz\Form\View\TraversableView;
use Berlioz\Form\View\ViewInterface;
use InvalidArgumentException;

abstract class AbstractTraversableElement extends AbstractElement implements TraversableElementInterface
{
    /** @var \Berlioz\Form\Element\ElementInterface[] Form elements */
    protected $list = [];

    /**
     * Empty list.
     */
    public function empty(): void
    {
        $this->list = [];
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->list);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->list);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->list[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset): ?ElementInterface
    {
        return isset($this->list[$offset]) ? $this->list[$offset] : null;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        if ($value instanceof ElementInterface) {
            if (is_null($offset) || mb_strlen($offset) == 0) {
                $this->list[] = $value;
            } else {
                $this->list[$offset] = $value;
            }
        } else {
            throw new InvalidArgumentException(sprintf('Form collection accept only "%s" class', ElementInterface::class));
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->list[$offset]);
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * @inheritdoc
     */
    public function build()
    {
        /** @var \Berlioz\Form\Element\ElementInterface $element */
        foreach ($this as $element) {
            $element->build();
        }
    }

    /**
     * @inheritdoc
     */
    public function buildView(): ViewInterface
    {
        $list =
            array_map(
                function (ElementInterface $element) {
                    return $element->buildView();
                },
                $this->list
            );

        return new TraversableView($this, [], $list);
    }
}