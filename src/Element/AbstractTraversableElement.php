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

namespace Berlioz\Form\Element;

use ArrayIterator;
use Berlioz\Form\View\TraversableView;
use Berlioz\Form\View\ViewInterface;
use InvalidArgumentException;

abstract class AbstractTraversableElement extends AbstractElement implements TraversableElementInterface
{
    protected array $list = [];

    /**
     * Empty list.
     */
    public function empty(): void
    {
        $this->list = [];
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->list);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->list);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->list[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): ?ElementInterface
    {
        return $this->list[$offset] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof ElementInterface) {
            throw new InvalidArgumentException(sprintf('Accept only "%s" class', ElementInterface::class));
        }

        if (is_null($offset) || mb_strlen((string)$offset) == 0) {
            $this->list[] = $value;
        } else {
            $this->list[$offset] = $value;
        }

        $value->setParent($this);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->list[$offset]);
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * @inheritDoc
     */
    public function build(): void
    {
        /** @var ElementInterface $element */
        foreach ($this as $element) {
            $element->build();
        }
    }

    /**
     * @inheritDoc
     */
    public function buildView(): ViewInterface
    {
        $list = array_map(fn(ElementInterface $element) => $element->buildView(), $this->list);

        return new TraversableView(
            $this,
            [
                'errors' => $this->getConstraints(),
                'required' => $this->getOption('required', false, true),
                'disabled' => $this->getOption('disabled', false, true),
                'readonly' => $this->getOption('readonly', false, true),
                'mapped' => $this->getMapped(),
            ],
            $list
        );
    }
}