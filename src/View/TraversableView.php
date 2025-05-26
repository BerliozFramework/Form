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

namespace Berlioz\Form\View;

use ArrayIterator;
use Berlioz\Form\Element\TraversableElementInterface;
use Berlioz\Form\Exception\FormException;

class TraversableView extends BasicView implements TraversableViewInterface
{
    /**
     * TraversableView constructor.
     *
     * @param TraversableElementInterface $src
     * @param array $variables
     * @param ViewInterface[] $list
     */
    public function __construct(
        TraversableElementInterface $src,
        array $variables = [],
        private array $list = []
    ) {
        parent::__construct($src, $variables);

        $this->list = array_filter($this->list, fn($value) => $value instanceof ViewInterface);
        array_walk($this->list, fn(ViewInterface $view) => $view->setParentView($this));
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
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->list);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet(mixed $offset): ?ViewInterface
    {
        return $this->list[$offset] ?? null;
    }

    /**
     * @inheritDoc
     * @throws FormException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new FormException('Not allowed to set element in view');
    }

    /**
     * @inheritDoc
     * @throws FormException
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new FormException('Not allowed to unset element in view');
    }

    /**
     * __isset() PHP magic method to test if sub element exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->list);
    }

    /**
     * Get sub element.
     *
     * @param string $name
     *
     * @return ViewInterface
     * @throws FormException
     */
    public function __get(string $name)
    {
        if (!$this->__isset($name)) {
            throw new FormException(sprintf('Unable to find sub element "%s" in view', $name));
        }

        return $this->list[$name];
    }

    /////////////////
    /// INSERTION ///
    /////////////////

    /**
     * Is inserted?
     *
     * @return bool
     */
    public function isInserted(): bool
    {
        if (count($this->list) == 0) {
            return parent::isInserted();
        }

        /** @var ViewInterface $view */
        foreach ($this as $view) {
            if (!$view->isInserted()) {
                return false;
            }
        }

        return true;
    }
}