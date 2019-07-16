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

namespace Berlioz\Form;

use Berlioz\Form\Exception\FormException;
use Berlioz\Form\View\ViewInterface;

class Group extends TraversableElement
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
        $data = ['parent'   => $this->getParent() ? $this->getParent()->getName() : null,
                 'children' => []];

        /** @var \Berlioz\Form\ElementInterface $element */
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
            throw new \InvalidArgumentException(sprintf('Form group accept only "%s" class', ElementInterface::class));
        }

        if (!is_string($offset)) {
            throw new \InvalidArgumentException('Elements in form group must be named');
        }

        parent::offsetSet($offset, $value);
        $value->setParent($this);
    }

    /**
     * Add an element to group.
     *
     * @param string        $name    Name of element
     * @param string|object $class   Class name of element
     * @param array         $options Options of element
     *
     * @return static
     */
    public function add(string $name, $class, array $options = [])
    {
        if (!(is_a($class, ElementInterface::class, true))) {
            throw new \InvalidArgumentException(sprintf('Class name must be a valid class/object and must be implements "%s" interface', ElementInterface::class));
        }

        if (!is_object($class)) {
            $options['name'] = $name;
            $this[$name] = new $class($options);
        } else {
            /** @var \Berlioz\Form\ElementInterface $class */
            foreach ($options as $optName => $optValue) {
                $class->setOption($optName, $optValue);
            }
            $class->setOption('name', $name);
            $this[$name] = $class;
        }

        return $this;
    }

    /**
     * Get transformer.
     *
     * @return \Berlioz\Form\Transformer|null
     */
    public function getTransformer(): ?Transformer
    {
        if (!is_null($transformer = $this->getOption('transformer'))) {
            if ($transformer instanceof Transformer) {
                return $transformer;
            }
        }

        return null;
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * @inheritdoc
     */
    public function getValue(bool $raw = false)
    {
        $values = [];

        /** @var \Berlioz\Form\ElementInterface $element */
        foreach ($this as $element) {
            $values[$element->getName()] = $element->getValue($raw);
        }

        return $values;
    }

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\FormException
     */
    public function setValue($values, bool $submitted = false)
    {
        if (!is_array($values)) {
            throw new FormException('Invalid form submission');
        }

        foreach ($values as $name => $value) {
            if (isset($this[$name])) {
                /** @var \Berlioz\Form\ElementInterface $element */
                $element = $this[$name];
                $element->setValue($value, $submitted);
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