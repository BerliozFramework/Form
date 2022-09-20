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
use Berlioz\Form\Exception\FormException;
use Berlioz\Form\View\TraversableView;

class Collection extends AbstractTraversableElement
{
    protected ?ElementInterface $prototype = null;
    protected array $submittedKeys = [];

    /**
     * Collection constructor.
     *
     * @param array $options Options
     *
     * @throws FormException
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
     * __clone() magic method.
     */
    public function __clone()
    {
        $this->submittedKeys = [];
        $this->prototype = clone $this->prototype;
        $this->prototype->setParent($this);
        parent::__clone();
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

        /** @var ElementInterface $element */
        foreach ($this as $element) {
            $data['children'][] = $element;
        }

        return $data;
    }

    /**
     * Get index of form element.
     *
     * @param ElementInterface $element
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
            $this->list[] = $element = clone $this->prototype;
            $element->setParent($this);
        }
    }

    /////////////////
    /// PROTOTYPE ///
    /////////////////

    /**
     * Get prototype.
     *
     * @return ElementInterface
     */
    public function getPrototype(): ElementInterface
    {
        return $this->prototype;
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
     * @inheritDoc
     */
    public function getFinalValue(): mixed
    {
        $values = [];

        /** @var ElementInterface $element */
        foreach ($this as $key => $element) {
            if (($form = $this->getForm()) &&
                $form->isSubmitted() &&
                !in_array($key, $this->submittedKeys)) {
                continue;
            }

            $values[$key] = $element->getFinalValue();
        }

        return $this->getTransformer()->fromForm($values, $this);
    }

    /**
     * @inheritDoc
     */
    public function setValue(mixed $value): void
    {
        $value = $this->getTransformer()->toForm($value, $this);

        // Complete collection
        $this->completeCollection(count($value));

        $max = $this->getOption('max_elements');
        $i = 0;

        // Sort values
        ksort($value);

        foreach ($value as $key => $aValue) {
            if (is_int($max) && $i > $max) {
                continue;
            }

            /** @var ElementInterface $element */
            if (isset($this[$key])) {
                $element = $this[$key];
            } else {
                $this[$key] = $element = clone $this->prototype;
                $element->setParent($this);

                // Callback
                $this->callCallback('complete', $this, $this[$key]);
            }

            $element->setValue($aValue);
            $i++;
        }

        // Complete collection
        $this->completeCollection(count($value));
    }

    /**
     * @inheritDoc
     */
    public function submitValue($value): void
    {
        $value = (array)$value;

        // Complete collection
        $this->completeCollection(count($value));

        // Sort values
        ksort($value);

        // Submitted keys
        $this->submittedKeys = array_keys($value);

        // Delete old elements
        $diff = array_diff(array_keys($this->list), $this->submittedKeys);
        foreach ($diff as $keyToDelete) {
            $this[$keyToDelete]->setParent(null);

            // Callback
            $this->callCallback('remove', $this, $this[$keyToDelete]);

            unset($this[$keyToDelete]);
        }

        // Add
        foreach ($value as $key => $aValue) {
            /** @var ElementInterface $element */
            if (isset($this[$key])) {
                $element = $this[$key];
                $element->submitValue($aValue);
                continue;
            }

            $this[$key] = $element = clone $this->prototype;
            $element->setParent($this);
            $element->submitValue($aValue);

            // Callback
            $this->callCallback('add', $this, $element);
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
        /** @var TraversableView $view */
        $view = parent::buildView();

        $prototype = $this->getPrototype();
        $prototypeView = $prototype?->buildView();
        $prototypeView?->setParentView($view);

        $view->mergeVars(
            [
                'type' => $this->getOption('type', 'collection'),
                'id' => $this->getId(),
                'name' => $this->getFormName(),
                'prototype' => $prototypeView,
                'editable' => $this->getOption('editable', true),
                'min_elements' => $this->getOption('min_elements'),
                'max_elements' => $this->getOption('max_elements'),
            ]
        );

        return $view;
    }
}