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

namespace Berlioz\Form\View;

use ArrayIterator;
use Berlioz\Form\Element\TraversableElementInterface;
use Berlioz\Form\Exception\FormException;

/**
 * Class TraversableView.
 *
 * @package Berlioz\Form\View
 */
class TraversableView extends BasicView implements TraversableViewInterface
{
    /** @var array List of sub elements */
    private $list = [];

    /**
     * TraversableView constructor.
     *
     * @param \Berlioz\Form\Element\TraversableElementInterface $src
     * @param array $variables
     * @param \Berlioz\Form\View\ViewInterface[] $list
     */
    public function __construct(TraversableElementInterface $src, array $variables = [], array $list = [])
    {
        parent::__construct($src, $variables);

        $this->list =
            array_filter(
                $list,
                function ($value) {
                    /** @var \Berlioz\Form\View\ViewInterface $value */
                    $value->setParentView($this);

                    return $value instanceof ViewInterface;
                }
            );
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->list);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->list);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->list[$offset] ?? null;
    }

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\FormException
     */
    public function offsetSet($offset, $value)
    {
        throw new FormException('Not allowed to set element in view');
    }

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\FormException
     */
    public function offsetUnset($offset)
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
     * @return mixed
     * @throws \Berlioz\Form\Exception\FormException
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
            return false;
        }

        /** @var \Berlioz\Form\View\ViewInterface $view */
        foreach ($this as $view) {
            if (!$view->isInserted()) {
                return false;
            }
        }

        return true;
    }
}