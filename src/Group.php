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

namespace Berlioz\Form;

use Berlioz\Form\Element\AbstractTraversableElement;
use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Element\TraversableElementInterface;
use Berlioz\Form\Exception\FormException;
use Berlioz\Form\View\TraversableView;
use InvalidArgumentException;

class Group extends AbstractTraversableElement
{
    protected object|null $mapped;

    /**
     * __debugInfo() magic method.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        $data = [
            'parent' => $this->getParent()?->getName(),
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
    public function mapObject(?object $object = null)
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
    public function getMappedObject(): object|null
    {
        return $this->mapped ?? null;
    }

    ///////////////////
    /// ARRAYACCESS ///
    ///////////////////

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        if (!is_string($offset)) {
            throw new InvalidArgumentException('Elements in form group must be named');
        }

        parent::offsetSet($offset, $value);
    }

    /**
     * Add an element to group.
     *
     * @param string $name Name of element
     * @param ElementInterface|string $class Class name of element
     * @param array $options Options of element
     *
     * @return static
     */
    public function add(string $name, ElementInterface|string $class, array $options = []): static
    {
        if (false === is_a($class, ElementInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Class name must be a valid class/object and must be implements "%s" interface',
                    ElementInterface::class
                )
            );
        }

        if ($class instanceof ElementInterface) {
            $class->setOption('name', $name);
            $this[$name] = $class;

            return $this;
        }

        $options['name'] = $name;
        $this[$name] = new $class($options);

        return $this;
    }

    /**
     * Add element.
     *
     * @param ElementInterface ...$element
     *
     * @return $this
     */
    public function addElement(ElementInterface ...$element): static
    {
        foreach ($element as $anElement) {
            $this[$anElement->getName()] = $anElement;
        }

        return $this;
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritDoc
     */
    public function getValue(): array
    {
        $values = [];

        /** @var ElementInterface $element */
        foreach ($this as $element) {
            $values[$element->getName()] = $element->getValue();
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function getFinalValue(): mixed
    {
        $values = [];

        /** @var ElementInterface $element */
        foreach ($this as $element) {
            $values[$element->getName()] = $element->getFinalValue();
        }

        return $this->getTransformer()->fromForm($values, $this);
    }

    /**
     * @inheritDoc
     */
    public function submitValue(mixed $value): void
    {
        if (!is_array($value)) {
            throw new FormException('Invalid type of value, array attempted');
        }

        /** @var ElementInterface $element */
        foreach ($this as $element) {
            if (!array_key_exists($element->getName(), $value)) {
                if ($element instanceof TraversableElementInterface) {
                    $element->submitValue([]);
                    continue;
                }

                $element->submitValue(null);
                continue;
            }

            $element->submitValue($value[$element->getName()]);
        }
    }

    /**
     * @inheritDoc
     */
    public function setValue(mixed $value): void
    {
        if (null === $value) {
            return;
        }

        if (!is_array($value)) {
            throw new FormException('Invalid type of value, array expected');
        }

        /** @var ElementInterface[] $this */
        foreach ($value as $name => $aValue) {
            if (isset($this[$name])) {
                $this[$name]->setValue($aValue);
            }
        }
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * @inheritDoc
     */
    public function buildView(): TraversableView
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
