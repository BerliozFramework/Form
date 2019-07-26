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
use Berlioz\Form\View\ViewInterface;
use Exception;
use Traversable;

/**
 * Class Choice.
 *
 * @package Berlioz\Form\Type
 */
class Choice extends AbstractType
{
    /** @var \Berlioz\Form\Type\ChoiceValue[] Choices value */
    private $choicesForView;
    /** @var \Berlioz\Form\Type\ChoiceValue[] Choices values */
    private $choices = [];

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
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\TypeException
     */
    public function getValue()
    {
        $value = [];

        $this->buildChoices();
        $selectedChoicesValue = $this->getSelectedChoicesValue();

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

        $this->buildChoices();
        $selectedChoicesValue = $this->getSelectedChoicesValue();

        foreach ($selectedChoicesValue as $choiceValue) {
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

        return parent::setValue($value);
    }

    /**
     * @inheritdoc
     */
    public function submitValue($value)
    {
        if (!is_array($value) && !$value instanceof Traversable) {
            $value = [$value];
        }

        return parent::submitValue($value);
    }

    /**
     * Get selected choices value.
     *
     * @return \Berlioz\Form\Type\ChoiceValue[]
     */
    private function getSelectedChoicesValue(): array
    {
        $found = [];

        $value = parent::getValue();
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        if (!is_null($value)) {
            foreach ($this->choices as $choiceValue) {
                if (in_array($choiceValue->getValue(), $value)
                    || in_array($choiceValue->getFinalValue(), $value, true)) {
                    $found[] = $choiceValue->setSelected(true);
                }
            }
        }

        return $found;
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
        if (is_callable($callback)) {
            return $callback($key, $value, $index);
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
                    throw new TypeException(sprintf('Unable to found getter of "%s" property of "%s" class', $callback, get_class($value)));
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
     *
     * @return \Berlioz\Form\Type\ChoiceValue
     * @throws \Berlioz\Form\Exception\TypeException
     */
    private function buildChoiceValue($key, $value, int $index): ChoiceValue
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
                ->setAttributes($attributes ?? []);

        $this->choices[$choiceValue->getValue()] = $choiceValue;

        return $choiceValue;
    }

    /**
     * Build choices.
     *
     * @return \Berlioz\Form\Type\ChoiceValue[][]
     * @throws \Berlioz\Form\Exception\TypeException
     */
    private function buildChoices(): array
    {
        if (is_null($this->choicesForView)) {
            $choices = $this->getOption('choices', []);

            $this->choicesForView = [];

            $index = 0;
            foreach ($choices as $key => $value) {
                if (is_array($value) || $value instanceof Traversable) {
                    foreach ($value as $key2 => $value2) {
                        $this->choicesForView[$key][] = $this->buildChoiceValue($key2, $value2, $index);
                        $index++;
                    }
                } else {
                    $this->choicesForView[] = $this->buildChoiceValue($key, $value, $index);
                    $index++;
                }
            }
        }

        return $this->choicesForView;
    }

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\TypeException
     */
    public function build()
    {
        parent::build();

        $this->buildChoices();
        $this->getSelectedChoicesValue();
    }

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\TypeException
     */
    public function buildView(): ViewInterface
    {
        $this->buildChoices();
        $this->getSelectedChoicesValue();

        $view = parent::buildView();
        $view->mergeVars(
            [
                'allow_clear' => $this->getOption('allow_clear', false),
                'expanded' => $this->getOption('expanded', false),
                'multiple' => $this->getOption('multiple', false),
                'choices' => $this->choicesForView ?? [],
            ]
        );

        return $view;
    }
}