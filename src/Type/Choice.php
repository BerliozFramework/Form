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

namespace Berlioz\Form\Type;

use Berlioz\Form\Exception\TypeException;
use Berlioz\Form\Transformer\ChoiceTransformerInterface;
use Berlioz\Form\View\ViewInterface;
use Closure;
use Exception;
use ReflectionFunction;
use ReflectionParameter;
use Traversable;

class Choice extends AbstractMultipleType
{
    private array $choices;
    private array $additionalChoices = [];

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
     * @inheritDoc
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
     * @throws TypeException
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
     * @inheritDoc
     * @throws TypeException
     */
    public function getValue(): mixed
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
     * @inheritDoc
     * @throws TypeException
     */
    public function getFinalValue(): mixed
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
     * @inheritDoc
     */
    public function setValue(mixed $value): void
    {
        if (!is_array($value) && !$value instanceof Traversable) {
            $value = [$value];
        }

        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        parent::setValue(array_filter($value, fn($v) => null !== $v));
        $this->treatUnknownValues();
    }

    /**
     * @inheritDoc
     */
    public function submitValue($value): void
    {
        if (!is_array($value) && !$value instanceof Traversable) {
            $value = [$value];
        }

        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        parent::submitValue(array_filter($value, fn($v) => null !== $v));
        $this->treatUnknownValues();
    }

    /**
     * Treat unknown values.
     *
     * @throws TypeException
     */
    private function treatUnknownValues(): void
    {
        /** @var ChoiceTransformerInterface $choiceTransformer */
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
     * @return ChoiceValue[]
     * @throws TypeException
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

        $value = array_map(
            function ($value) {
                if (is_scalar($value)) {
                    return $value;
                }

                return $this->choiceCallback('choice_value', null, $value, null);
            },
            $value
        );

        $found = [];

        foreach ($this->buildChoices() as $choiceValue) {
            if (empty(array_keys($value, $choiceValue->getValue()))) {
                continue;
            }

            $choiceValue->setSelected(true);
            $found[] = $choiceValue;
        }

        return $found;
    }

    /**
     * Update preferred choices.
     *
     * @throws TypeException
     */
    private function updatePreferredChoices(): void
    {
        $preferredChoices = $this->getOption('preferred_choices');

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
     * @param int|string|null $key Key of choice
     * @param mixed $value Value of choice
     * @param int|null $index Index of choice
     *
     * @return mixed
     * @throws TypeException
     */
    private function choiceCallback(string $callbackName, int|string|null $key, mixed $value, ?int $index): mixed
    {
        if (is_null($callback = $this->getOption($callbackName))) {
            return null;
        }

        // Callable?
        if ($callback instanceof Closure) {
            try {
                $reflection = new ReflectionFunction($callback);
                $parameters = array_map(
                    fn(ReflectionParameter $param) => $param->getName(),
                    $reflection->getParameters()
                );
                $parameters = array_intersect_key(
                    ['key' => $key, 'value' => $value, 'index' => $index],
                    array_fill_keys($parameters, null)
                );

                return $reflection->invokeArgs($parameters);
            } catch (Exception $exception) {
                throw new TypeException('Unable to call closure callback', previous: $exception);
            }
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
                } catch (Exception $exception) {
                    throw new TypeException(
                        sprintf(
                            'Unable to found getter of "%s" property of "%s" class',
                            $callback,
                            get_class($value)
                        ),
                        previous: $exception
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
     * @return ChoiceValue
     * @throws TypeException
     */
    private function buildChoiceValue(int|string $key, mixed $value, int $index, ?string $group = null): ChoiceValue
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

        $choiceValue = new ChoiceValue();
        $choiceValue->setLabel($label);
        $choiceValue->setValue($rawValue);
        $choiceValue->setFinalValue($value);
        $choiceValue->setGroup($group);
        $choiceValue->setAttributes($attributes ?? []);

        return $choiceValue;
    }

    /**
     * Build choices.
     *
     * @return ChoiceValue[]
     * @throws TypeException
     */
    private function buildChoices(): array
    {
        if (null !== ($this->choices ?? null)) {
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
     * @return ChoiceValue[][]
     * @throws TypeException
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
     * @inheritDoc
     * @throws TypeException
     */
    public function build(): void
    {
        parent::build();
        $this->updateSelectedChoices();
        $this->updatePreferredChoices();
    }

    /**
     * @inheritDoc
     * @throws TypeException
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