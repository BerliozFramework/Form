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

namespace Berlioz\Form\View;

use Berlioz\Form\Exception\FormException;
use Berlioz\Form\TraversableElementInterface;

class TraversableView extends BasicView implements \IteratorAggregate
{
    /** @var array List of sub elements */
    private $list = [];

    /**
     * TraversableView constructor.
     *
     * @param \Berlioz\Form\TraversableElementInterface $src
     * @param array                                     $options
     * @param \Berlioz\Form\View\ViewInterface[]        $list
     */
    public function __construct(TraversableElementInterface $src, array $options = [], array $list = [])
    {
        parent::__construct($src, $options);

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
        return new \ArrayIterator($this->list);
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
        /** @var \Berlioz\Form\View\ViewInterface $view */
        foreach ($this as $view) {
            if (!$view->isInserted()) {
                return false;
            }
        }

        return true;
    }
}