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

/**
 * Class Collection.
 *
 * @package Berlioz\Form
 */
class Collection extends AbstractTraversableElement
{
    /** @var \Berlioz\Form\Element\ElementInterface Prototype */
    protected $prototype;
    /** @var array Submitted keys */
    protected $submittedKeys = [];

    /**
     * Collection constructor.
     *
     * @param array $options Options
     *
     * @throws \Berlioz\Form\Exception\FormException
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['prototype'])) {
            throw new FormException('Collections must be define "prototype" option value');
        }

        if (!is_object($this->prototype = $options['prototype'])) {
            $this->prototype = new $options['prototype'];
        }

        if (!$this->prototype instanceof ElementInterface) {
            throw new FormException(
                sprintf('"prototype" option of collection mus be implement "%s" class', ElementInterface::class)
            );
        }

        $this->prototype->setParent($this);

        // Default options
        $options = array_replace_recursive(
            [
                'editable' => true,
                'min_elements' => 1,
                'max_elements' => null,
            ],
            $options
        );

        parent::__construct($options);

        // Complete collection
        $this->completeCollection();
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
            'prototype' => $this->prototype,
            'children' => [],
        ];

        /** @var \Berlioz\Form\Element\ElementInterface $element */
        foreach ($this as $element) {
            $data['children'][] = $element;
        }

        return $data;
    }

    /**
     * Get index of form element.
     *
     * @param \Berlioz\Form\Element\ElementInterface $element
     *
     * @return false|int|string
     */
    public function indexOf(ElementInterface $element)
    {
        if (($index = array_search($element, $this->list, true)) === false) {
            if ($this->getPrototype() === $element) {
                return '___name___';
            }

            return false;
        }

        return $index;
    }

    /**
     * Complete collection.
     *
     * @param int|null $nb Number
     *
     * @return void
     */
    protected function completeCollection(int $nb = null): void
    {
        // Complete by elements
        $nbElements = count($this);
        $minElements = max($this->getOption('min_elements', 0), !is_null($nb) ? $nb : 0);

        if (!is_null($this->getOption('max_elements'))) {
            $minElements = min($minElements, $this->getOption('max_elements'));
        }

        for ($i = $nbElements; $i < $minElements; $i++) {
            $this->list[] = clone $this->prototype;
        }
    }

    /////////////////
    /// PROTOTYPE ///
    /////////////////

    /**
     * Get prototype.
     *
     * @return \Berlioz\Form\Element\ElementInterface
     */
    public function getPrototype(): ElementInterface
    {
        return $this->prototype;
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
        foreach ($this as $key => $element) {
            if (($form = $this->getForm()) &&
                $form->isSubmitted() &&
                !in_array($key, $this->submittedKeys)) {
                continue;
            }

            $values[$key] = $element->getValue();
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
        foreach ($this as $key => $element) {
            if (($form = $this->getForm()) &&
                $form->isSubmitted() &&
                !in_array($key, $this->submittedKeys)) {
                continue;
            }

            $values[$key] = $element->getFinalValue();
        }

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function setValue($values)
    {
        // Complete collection
        $this->completeCollection(count($values));

        $max = $this->getOption('max_elements');
        $i = 0;

        // Sort values
        ksort($values);

        foreach ($values as $key => $value) {
            if (is_int($max) && $i > $max) {
                continue;
            }

            /** @var \Berlioz\Form\Element\ElementInterface $element */
            if (isset($this[$key])) {
                $element = $this[$key];
            } else {
                $this[$key] = $element = clone $this->prototype;
                $element->setParent($this);

                // Callback
                $this->callCallback('complete', $this, $this[$key]);
            }

            $element->setValue($value);
            $i++;
        }

        // Complete collection
        $this->completeCollection(count($values));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function submitValue($values)
    {
        $values = (array)$values;

        // Complete collection
        $this->completeCollection(count($values));

        // Sort values
        ksort($values);

        // Submitted keys
        $this->submittedKeys = array_keys($values);

        // Delete old elements
        $diff = array_diff(array_keys($this->list), $this->submittedKeys);
        foreach ($diff as $keyToDelete) {
            $this[$keyToDelete]->setParent(null);

            // Callback
            $this->callCallback('remove', $this, $this[$keyToDelete]);

            unset($this[$keyToDelete]);
        }

        // Add
        foreach ($values as $key => $value) {
            /** @var \Berlioz\Form\Element\ElementInterface $element */
            if (isset($this[$key])) {
                $element = $this[$key];
                $element->submitValue($value);
                continue;
            }

            $this[$key] = $element = clone $this->prototype;
            $element->setParent($this);
            $element->submitValue($value);

            // Callback
            $this->callCallback('add', $this, $element);
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
        /** @var \Berlioz\Form\View\TraversableView $view */
        $view = parent::buildView();
        $view->mergeVars(
            [
                'type' => $this->getOption('type', 'collection'),
                'id' => $this->getId(),
                'name' => $this->getFormName(),
                'prototype' => $this->getPrototype()->buildView()->setParentView($view),
                'editable' => $this->getOption('editable', true),
                'min_elements' => $this->getOption('min_elements'),
                'max_elements' => $this->getOption('max_elements'),
            ]
        );

        return $view;
    }
}