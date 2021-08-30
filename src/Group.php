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

namespace Berlioz\Form;

use Berlioz\Form\Element\AbstractTraversableElement;
use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Element\TraversableElementInterface;
use Berlioz\Form\Exception\FormException;
use Berlioz\Form\View\ViewInterface;
use InvalidArgumentException;

/**
 * Class Group.
 */
class Group extends AbstractTraversableElement
{
    /** @var object|array|null Mapped object or array */
    protected $mapped;

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

        /** @var ElementInterface $element */
        foreach ($this as $element) {
            $data['children'][$element->getName()] = $element;
        }

        return $data;
    }

    ///////////////
    /// MAPPING ///
    ///////////////

    /**
     * Map an object.
     *
     * @param object|null $object
     *
     * @throws FormException
     */
    public function mapObject($object = null)
    {
        if (!is_object($object) && !is_null($object)) {
            throw new FormException(sprintf('Parameter given must be an object, "%s" given', gettype($object)));
        }

        $this->mapped = $object;
        $this->setOption('mapped', null !== $object);
    }

    /**
     * Get mapped object.
     *
     * @return object|null
     */
    public function getMappedObject()
    {
        return $this->mapped;
    }

    ///////////////////
    /// ARRAYACCESS ///
    ///////////////////

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
            /** @var ElementInterface $class */
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

        /** @var ElementInterface $element */
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

        /** @var ElementInterface $element */
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

        /** @var ElementInterface $element */
        foreach ($this as $element) {
            if (!array_key_exists($element->getName(), $values)) {
                if ($element instanceof TraversableElementInterface) {
                    $element->submitValue([]);
                    continue;
                }

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

        /** @var ElementInterface[] $this */
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
        $view->mergeVars(
            [
                'type' => $this->getOption('type', 'group'),
                'mapped' => $this->getMapped()
            ]
        );

        return $view;
    }
}
