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

namespace Berlioz\Form\Type;

use Berlioz\Form\AbstractType;
use Berlioz\Form\View\ViewInterface;

class Choice extends AbstractType
{
    /** @var \Berlioz\Form\Type\ChoiceValue[] Choices value */
    private $choicesForView = [];
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
     */
    public function getValue(bool $raw = false)
    {
        $value = [];
        $selectedChoicesValue = $this->getSelectedChoicesValue();

        foreach ($selectedChoicesValue as $choiceValue) {
            if ($raw) {
                $value[] = $choiceValue->getValue();
            } else {
                $value[] = $choiceValue->getFinalValue();
            }
        }

        if (!$this->getOption('multiple', false)) {
            if (($value = reset($value)) === false) {
                $value = null;
            }
        }

        // Transformer
        if (!$raw && !is_null($transformer = $this->getTransformer())) {
            return $transformer->fromForm($value);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value, bool $submitted = false)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        return parent::setValue($value, $submitted);
    }

    /**
     * Get selected choices value.
     *
     * @return \Berlioz\Form\Type\ChoiceValue[]
     */
    private function getSelectedChoicesValue(): array
    {
        $found = [];

        foreach ($this->choices as $choiceValue) {
            if (in_array($choiceValue->getValue(), parent::getValue(true))
                || in_array($choiceValue->getFinalValue(), parent::getValue(true))) {
                $found[] = $choiceValue->setSelected(true);
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
     * @param string     $callbackName Callback name
     * @param int|string $key          Key of choice
     * @param mixed      $value        Value of choice
     * @param int        $index        Index of choice
     *
     * @return null|mixed
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
                $result = b_property_get($value, $callback, $exists);

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
     * @param int|string $key   Key of choice
     * @param mixed      $value Value of choice
     * @param int        $index Index of choice
     *
     * @return \Berlioz\Form\Type\ChoiceValue
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
     */
    private function buildChoices(): array
    {
        $choices = $this->getOption('choices', []);

        $this->choicesForView = [];

        $index = 0;
        foreach ($choices as $key => $value) {
            if (is_array($value) || $value instanceof \Traversable) {
                foreach ($value as $key2 => $value2) {
                    $this->choicesForView[$key][] = $this->buildChoiceValue($key2, $value2, $index);
                    $index++;
                }
            } else {
                $this->choicesForView[] = $this->buildChoiceValue($key, $value, $index);
                $index++;
            }
        }

        return $this->choicesForView;
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        parent::build();

        $this->buildChoices();
        $this->getSelectedChoicesValue();
    }

    /**
     * @inheritdoc
     */
    public function buildView(): ViewInterface
    {
        $view = parent::buildView();
        $view->mergeVars(['allow_clear' => $this->getOption('allow_clear', false),
                          'expanded'    => $this->getOption('expanded', false),
                          'multiple'    => $this->getOption('multiple', false),
                          'choices'     => $this->choicesForView ?? []]);

        return $view;
    }
}