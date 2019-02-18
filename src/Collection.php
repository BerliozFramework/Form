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
use Berlioz\Form\View\TraversableView;
use Berlioz\Form\View\ViewInterface;

class Collection extends TraversableElement
{
    /** @var \Berlioz\Form\ElementInterface Prototype element */
    protected $prototype;

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
            throw new FormException(sprintf('"prototype" option of collection mus be implement "%s" class', ElementInterface::class));
        }

        $this->prototype->setParent($this);

        // Default options
        $options = array_replace_recursive(['editable'     => true,
                                            'min_elements' => 1,
                                            'max_elements' => null],
                                           $options);

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
        $data = ['parent'    => $this->getParent() ? $this->getParent()->getName() : null,
                 'prototype' => $this->prototype,
                 'children'  => []];

        /** @var \Berlioz\Form\ElementInterface $element */
        foreach ($this as $element) {
            $data['children'][] = $element;
        }

        return $data;
    }

    public function getPrototype(): ElementInterface
    {
        return $this->prototype;
    }

    /**
     * Complete collection.
     *
     * @param int|null $nb Number
     */
    protected function completeCollection(int $nb = null)
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

    /**
     * Get index of form element.
     *
     * @param \Berlioz\Form\ElementInterface $formElement
     *
     * @return false|int|string
     */
    public function indexOf(ElementInterface $formElement)
    {
        if (($index = array_search($formElement, $this->list, true)) === false) {
            if ($this->getPrototype() === $formElement) {
                return '___name___';
            }

            return false;
        }

        return $index;
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
            $values[] = $element->getValue($raw);
        }

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function setValue($values, bool $submitted = false)
    {
        // Complete collection
        $this->completeCollection(count($values));

        $i = 0;

        // Sort values
        ksort($values);

        foreach ($values as $value) {
            if (!is_int($this->getOption('max_elements')) || $i < $this->getOption('max_elements')) {
                if (isset($this[$i])) {
                    /** @var \Berlioz\Form\ElementInterface $element */
                    $element = $this[$i];
                } else {
                    $this[$i] = $element = clone $this->prototype;
                    $element->setParent($this);
                }

                $element->setValue($value, $submitted);
                $i++;
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
        /** @var \Berlioz\Form\View\TraversableView $view */
        $view = parent::buildView();
        $view->mergeVars(['type'         => $this->getOption('type', 'collection'),
                          'id'           => $this->getId(),
                          'name'         => $this->getFormName(),
                          'prototype'    => $this->getPrototype()->buildView()->setParentView($view),
                          'editable'     => $this->getOption('editable', true),
                          'min_elements' => $this->getOption('min_elements'),
                          'max_elements' => $this->getOption('max_elements')]);

        return $view;
    }
}