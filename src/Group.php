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

namespace Berlioz\Form;

use Berlioz\Form\Element\AbstractTraversableElement;
use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\FormException;
use Berlioz\Form\View\ViewInterface;
use InvalidArgumentException;

/**
 * Class Group.
 *
 * @package Berlioz\Form
 */
class Group extends AbstractTraversableElement
{
    /**
     * __clone() magic method.
     */
    public function __clone()
    {
        foreach ($this->list as &$element) {
            $element = clone $element;
            $element->setParent($this);
        }
    }

    /**
     * __debugInfo() magic method.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        $data = [
            'parent' => $this->getParent() ? $this->getParent()->getName() : null,
            'children' => [],
        ];

        /** @var \Berlioz\Form\Element\ElementInterface $element */
        foreach ($this as $element) {
            $data['children'][$element->getName()] = $element;
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        if (!($value instanceof ElementInterface)) {
            throw new InvalidArgumentException(sprintf('Form group accept only "%s" class', ElementInterface::class));
        }

        if (!is_string($offset)) {
            throw new InvalidArgumentException('Elements in form group must be named');
        }

        parent::offsetSet($offset, $value);
    }

    /**
     * Add an element to group.
     *
     * @param string $name Name of element
     * @param string|object $class Class name of element
     * @param array $options Options of element
     *
     * @return static
     */
    public function add(string $name, $class, array $options = [])
    {
        if (!(is_a($class, ElementInterface::class, true))) {
            throw new InvalidArgumentException(
                sprintf(
                    'Class name must be a valid class/object and must be implements "%s" interface',
                    ElementInterface::class
                )
            );
        }

        if (!is_object($class)) {
            $options['name'] = $name;
            $this[$name] = new $class($options);
        } else {
            /** @var \Berlioz\Form\Element\ElementInterface $class */
            foreach ($options as $optName => $optValue) {
                $class->setOption($optName, $optValue);
            }
            $class->setOption('name', $name);
            $this[$name] = $class;
        }

        return $this;
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        $values = [];

        /** @var \Berlioz\Form\Element\ElementInterface $element */
        foreach ($this as $element) {
            $values[$element->getName()] = $element->getValue();
        }

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function getFinalValue()
    {
        $values = [];

        /** @var \Berlioz\Form\Element\ElementInterface $element */
        foreach ($this as $element) {
            $values[$element->getName()] = $element->getFinalValue();
        }

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function submitValue($values)
    {
        if (!is_array($values)) {
            throw new FormException('Invalid type of value, array attempted');
        }

        /** @var \Berlioz\Form\Element\ElementInterface $element */
        foreach ($this as $element) {
            if (!array_key_exists($element->getName(), $values)) {
                $element->submitValue(null);
                continue;
            }

            $element->submitValue($values[$element->getName()]);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setValue($values)
    {
        if (!is_array($values)) {
            throw new FormException('Invalid type of value, array attempted');
        }

        /** @var \Berlioz\Form\Element\ElementInterface[] $this */
        foreach ($values as $name => $value) {
            if (isset($this[$name])) {
                $this[$name]->setValue($value);
            }
        }

        return $this;
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * @inheritdoc
     */
    public function buildView(): ViewInterface
    {
        $view = parent::buildView();
        $view->mergeVars(['type' => $this->getOption('type', 'group')]);

        return $view;
    }
}