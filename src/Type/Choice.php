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

namespace Berlioz\Form\Type;

use Berlioz\Form\Exception\TypeException;
use Berlioz\Form\Transformer\ChoiceTransformerInterface;
use Berlioz\Form\View\ViewInterface;
use Closure;
use Exception;
use Traversable;

/**
 * Class Choice.
 *
 * @package Berlioz\Form\Type
 */
class Choice extends AbstractType
{
    /** @var \Berlioz\Form\Type\ChoiceValue[] Choices values */
    private $choices;
    /** @var \Berlioz\Form\Type\ChoiceValue[] Additional choices values */
    private $additionalChoices = [];

    /**
     * Choice constructor.
     *
     * @param array $options Options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->value = [];
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'choice';
    }

    /////////////
    /// VALUE ///
    /////////////

    /**
     * Get values.
     *
     * @return array
     * @throws \Berlioz\Form\Exception\TypeException
     */
    protected function getValues(): array
    {
        $values = [];

        foreach ($this->buildChoices() as $choice) {
            $values[] = $choice->getValue();
        }

        return $values;
    }

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\TypeException
     */
    public function getValue()
    {
        $value = [];
        $selectedChoicesValue = $this->updateSelectedChoices();

        foreach ($selectedChoicesValue as $choiceValue) {
            $value[] = $choiceValue->getValue();
        }

        if (!$this->getOption('multiple', false)) {
            if (($value = reset($value)) === false) {
                $value = null;
            }
        }

        return $value;
    }

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\TypeException
     */
    public function getFinalValue()
    {
        $value = [];
        $selectedChoices = $this->updateSelectedChoices();

        foreach ($selectedChoices as $choiceValue) {
            $value[] = $choiceValue->getFinalValue();
        }

        if (!$this->getOption('multiple', false)) {
            if (($value = reset($value)) === false) {
                $value = null;
            }
        }

        return $this->getTransformer()->fromForm($value, $this);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        if (!is_array($value) && !$value instanceof Traversable) {
            $value = [$value];
        }

        parent::setValue($value);
        $this->treatUnknownValues();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function submitValue($value)
    {
        if (!is_array($value) && !$value instanceof Traversable) {
            $value = [$value];
        }

        parent::submitValue($value);
        $this->treatUnknownValues();

        return $this;
    }

    /**
     * Treat unknown values.
     *
     * @throws \Berlioz\Form\Exception\TypeException
     */
    private function treatUnknownValues()
    {
        /** @var \Berlioz\Form\Transformer\ChoiceTransformerInterface $choiceTransformer */
        if (!(($choiceTransformer = $this->getOption('choice_transformer')) instanceof ChoiceTransformerInterface)) {
            return;
        }

        $values = array_merge($this->value ?? [], $this->submittedValue ?? []);

        $availableValues = $this->getValues();
        $unknownChoices = [];

        foreach ($values as $value) {
            if (in_array($value, $availableValues)) {
                continue;
            }
            $unknownChoices[] = $value;
        }

        $this->additionalChoices = $choiceTransformer->toForm($unknownChoices, $this);
        $this->additionalChoices = array_filter(
            $this->additionalChoices,
            function ($choiceValue) {
                return $choiceValue instanceof ChoiceValue;
            }
        );
    }

    /**
     * Update selected choices.
     *
     * @return \Berlioz\Form\Type\ChoiceValue[]
     * @throws \Berlioz\Form\Exception\TypeException
     */
    private function updateSelectedChoices(): array
    {
        $value = parent::getValue();
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        if (null === $value) {
            return [];
        }

        $found = [];

        foreach ($this->buildChoices() as $choiceValue) {
            if (empty(array_keys($value, $choiceValue->getValue()))) {
                // Final value not scalar
                if (false === is_scalar($choiceValue->getFinalValue())) {
                    continue;
                }

                if (empty(array_keys($value, $choiceValue->getFinalValue()))) {
                    continue;
                }
            }

            $found[] = $choiceValue->setSelected(true);
        }

        return $found;
    }

    /**
     * Update preferred choices.
     *
     * @throws TypeException
     */
    private function updatePreferredChoices()
    {
        $preferredChoices = $this->getOption('preferred_choices', null);

        $index = 0;
        foreach ($this->buildChoices() as $choiceValue) {
            if (null === $preferredChoices) {
                $choiceValue->setPreferred(false);
                continue;
            }

            if (is_array($preferredChoices)) {
                $choiceValue->setPreferred(in_array($choiceValue->getValue(), $preferredChoices));
                continue;
            }

            if (is_callable($preferredChoices)) {
                $choiceValue->setPreferred($preferredChoices($choiceValue, $index) == true);
                continue;
            }

            $choiceValue->setPreferred(false);
            $index++;
        }
    }

    /////////////
    /// BUILD ///
    /////////////

    /**
     * Callback for each choice.
     *
     * @param string $callbackName Callback name
     * @param int|string $key Key of choice
     * @param mixed $value Value of choice
     * @param int $index Index of choice
     *
     * @return null|mixed
     * @throws \Berlioz\Form\Exception\TypeException
     */
    private function choiceCallback(string $callbackName, $key, $value, int $index)
    {
        if (is_null($callback = $this->getOption($callbackName))) {
            return null;
        }

        // Callable?
        if ($callback instanceof Closure) {
            return $callback->call($this, $key, $value, $index);
        }

        // Array?
        if (is_array($callback)) {
            if (!is_string($value) && !is_int($value)) {
                return null;
            }

            return $callback[$value] ?? null;
        }

        // Value is object?
        if (is_string($callback) && !empty($callback)) {
            if (is_object($value)) {
                $exists = false;
                try {
                    $result = b_get_property_value($value, $callback, $exists);
                } catch (Exception $e) {
                    throw new TypeException(
                        sprintf('Unable to found getter of "%s" property of "%s" class', $callback, get_class($value))
                    );
                }

                if ($exists) {
                    return $result;
                }

                if (method_exists($value, $callback)) {
                    return call_user_func([$value, $callback]);
                }
            }

            return $callback;
        }

        return null;
    }

    /**
     * Build choice value object.
     *
     * @param int|string $key Key of choice
     * @param mixed $value Value of choice
     * @param int $index Index of choice
     * @param string|null $group Group of choice
     *
     * @return \Berlioz\Form\Type\ChoiceValue
     * @throws \Berlioz\Form\Exception\TypeException
     */
    private function buildChoiceValue($key, $value, int $index, ?string $group = null): ChoiceValue
    {
        // Value
        $valueIsObject = is_object($value);
        if (is_null($rawValue = $this->choiceCallback('choice_value', $key, $value, $index))) {
            if ($valueIsObject) {
                $rawValue = $index;
            } else {
                $rawValue = $value;
            }
        }

        // Label
        if (is_null($label = $this->choiceCallback('choice_label', $key, $value, $index))) {
            if (is_null($label = $this->choiceCallback('choice_label', $key, $rawValue, $index))) {
                $label = $key;
            }
        }

        // Attributes
        if (is_null($attributes = $this->choiceCallback('choice_attributes', $key, $value, $index))) {
            if (is_null($attributes = $this->choiceCallback('choice_attributes', $key, $rawValue, $index))) {
                $attributes = [];
            }
        }

        $choiceValue =
            (new ChoiceValue())
                ->setLabel($label)
                ->setValue($rawValue)
                ->setFinalValue($value)
                ->setGroup($group)
                ->setAttributes($attributes ?? []);

        return $choiceValue;
    }

    /**
     * Build choices.
     *
     * @return \Berlioz\Form\Type\ChoiceValue[]
     * @throws \Berlioz\Form\Exception\TypeException
     */
    private function buildChoices(): array
    {
        if (null !== $this->choices) {
            return array_merge($this->choices, $this->additionalChoices);
        }

        $choices = $this->getOption('choices', []);
        $this->choices = [];

        $index = 0;
        foreach ($choices as $key => $value) {
            if (is_array($value) || $value instanceof Traversable) {
                foreach ($value as $key2 => $value2) {
                    $this->choices[] = $this->buildChoiceValue($key2, $value2, $index, $key);
                    $index++;
                }
            } else {
                $this->choices[] = $this->buildChoiceValue($key, $value, $index);
                $index++;
            }
        }

        return $this->choices;
    }

    /**
     * Build choices for view.
     *
     * @return \Berlioz\Form\Type\ChoiceValue[][]
     * @throws \Berlioz\Form\Exception\TypeException
     */
    private function buildChoicesForView(): array
    {
        $choices = $this->buildChoices();

        // Order choices
        uasort(
            $choices,
            function (ChoiceValue $choiceValue1, ChoiceValue $choiceValue2) use ($choices) {
                if ($choiceValue1->isPreferred() == $choiceValue2->isPreferred()) {
                    $choiceKey1 = array_search($choiceValue1, $choices);
                    $choiceKey2 = array_search($choiceValue2, $choices);

                    if ($choiceKey1 == $choiceKey2) {
                        return 0;
                    }

                    return $choiceKey1 > $choiceKey2 ? 1 : -1;
                }

                if ($choiceValue1->isPreferred()) {
                    return -1;
                }

                return 1;
            }
        );

        $choicesForView = [];

        /** @var  $choice */
        foreach ($choices as $choice) {
            $group = $choice->getGroup();

            if (null === $group) {
                $choicesForView[$choice->getValue()] = $choice;
                continue;
            }

            $choicesForView[$group][$choice->getValue()] = $choice;
        }

        return $choicesForView;
    }

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\TypeException
     */
    public function build()
    {
        parent::build();
        $this->updateSelectedChoices();
        $this->updatePreferredChoices();
    }

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\TypeException
     */
    public function buildView(): ViewInterface
    {
        $this->updateSelectedChoices();
        $this->updatePreferredChoices();

        $view = parent::buildView();
        $view->mergeVars(
            [
                'allow_clear' => $this->getOption('allow_clear', false),
                'expanded' => $this->getOption('expanded', false),
                'multiple' => $this->getOption('multiple', false),
                'choices' => $this->buildChoicesForView(),
            ]
        );

        return $view;
    }
}