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

use Berlioz\Form\FormType;

class Choice extends FormType
{
    const TYPE = 'choice';
    /** @var ChoiceValue[] Choice values */
    private $choices;

    /**
     * Choice constructor.
     *
     * @param string $name    Name
     * @param array  $options Options
     */
    public function __construct(string $name, array $options = [])
    {
        $this->getOptions()->setOptions(['expanded'    => false,
                                         'multiple'    => false,
                                         'allow_clear' => false,
                                         'choices'     => []]);

        parent::__construct($name, $options);
    }

    /**
     * Get form name.
     *
     * @return string
     */
    public function getFormName(): string
    {
        return parent::getFormName() . ($this->getOptions()->get('multiple') == true ? '[]' : '');
    }

    /**
     * Get choices.
     *
     * @return \Berlioz\Form\Type\ChoiceValue[]|\Berlioz\Form\Type\ChoiceValue[][]
     */
    private function getChoices()
    {
        if (is_null($this->choices)) {
            $this->choices = [];

            if ($this->getOptions()->get('expanded') == true) {
                if ($this->getOptions()->get('multiple') == false) {
                    $choice_type = 'radio';
                } else {
                    $choice_type = 'checkbox';
                }
            } else {
                $choice_type = 'option';
            }

            $newChoice =
                function ($choice_value, $choice_key, $index) use ($choice_type) {
                    $label = $this->choiceCallback('choice_label', $choice_value, $choice_key, $index) ?? $choice_key;
                    $value = $this->choiceCallback('choice_value', $choice_value, $choice_key, $index) ?? $choice_value;
                    $attributes = $this->choiceCallback('choice_attributes', $choice_value, $choice_key, $index) ?? [];

                    if (!is_null($label) && !is_null($value) && is_array($attributes)) {
                        $choice = new ChoiceValue;
                        $choice->setType($choice_type);
                        $choice->setLabel($label);
                        $choice->setValue($value);
                        $choice->setAttributes($attributes);
                        $choice->setOriginalData($choice_value);

                        return $choice;
                    } else {
                        return null;
                    }
                };

            if ($this->getOptions()->is_array('choices') || $this->getOptions()->is_a('choices', '\ArrayAccess')) {
                $index = 0;
                foreach ($this->getOptions()->get('choices') as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $key2 => $value2) {
                            if (!is_null($fChoice = $newChoice($value2, $key2, $index))) {
                                $this->choices[$key][] = $fChoice;
                            }

                            $index++;
                        }
                    } else {
                        if (!is_null($fChoice = $newChoice($value, $key, $index))) {
                            $this->choices[] = $fChoice;
                        }

                        $index++;
                    }
                }
            }


            // Set selected !
            // WARNING: Do not move this part before $this->choices completion
            {
                $values = parent::getValue();

                // Values
                if (!is_array($values) && !is_a($values, '\ArrayAccess')) {
                    $values = [$values];
                }

                foreach ($this->choices as $choice) {
                    //$value = $this->choiceCallback('choice_value', $choice->getOriginalData(), $choice_key, $index) ?? $choice_value;

                    if (is_array($choice)) {
                        foreach ($choice as $choice2) {
                            $choice2->setSelected(false);

                            foreach ($values as $value) {
                                if ($choice2->getOriginalData() == $value || $choice2->getValue() == $value) {
                                    $choice2->setSelected(true);
                                }
                            }
                        }
                    } else {
                        $choice->setSelected(false);

                        foreach ($values as $value) {
                            if ($choice->getOriginalData() == $value || $choice->getValue() == $value) {
                                $choice->setSelected(true);
                            }
                        }
                    }
                }
            }
        }

        return $this->choices;
    }

    private function choiceCallback($callback_name, $choice_value, $choice_key, $index = null)
    {
        $result = null;

        if ($this->getOptions()->is_callable($callback_name)) {
            $result = $this->getOptions()->get($callback_name)($choice_value, $choice_key, $index);
        } else {
            if ($this->getOptions()->is_array($callback_name)) {
                if (isset($this->getOptions()->get($callback_name)[$choice_key])) {
                    $result = $this->getOptions()->get($callback_name)[$choice_key];
                } else {
                    $result = null;
                }
            } else {
                if ($this->getOptions()->is_string($callback_name) && !$this->getOptions()->is_empty($callback_name)) {
                    if (is_object($choice_value)) {
                        $exists = false;
                        $result = b_property_get($choice_value, $this->getOptions()->get($callback_name), $exists);

                        if ($exists === false) {
                            $result = null;
                        }
                    } else {
                        $result = $this->getOptions()->get($callback_name);
                    }
                } else {
                    $result = null;
                }
            }
        }

        return $result;
    }

    /**
     * Get value.
     *
     * @return mixed
     */
    public function getValue()
    {
        $fValue = [];
        $choices = $this->getChoices();

        foreach ($choices as $key => $choice_group) {
            if (is_array($choice_group)) {
                /** @var array $choice_group */
                /** @var \Berlioz\Form\Type\ChoiceValue $choice */
                foreach ($choice_group as $choice) {
                    if ($choice->isSelected()) {
                        $fValue[] = $choice->getOriginalData();
                    }
                }
            } else {
                /** @var \Berlioz\Form\Type\ChoiceValue $choice */
                $choice = $choice_group;

                if ($choice->isSelected()) {
                    $fValue[] = $choice->getOriginalData();
                }
            }
        }

        // Not multiple ?
        if ($this->getOptions()->get('multiple') == false) {
            $fValue = reset($fValue);
        }

        return $fValue;
    }

    /**
     * Get template data.
     *
     * @param array $options Options
     *
     * @return array
     */
    public function getTemplateData(array $options = []): array
    {
        return parent::getTemplateData(b_array_merge_recursive($options,
                                                               ['choices'     => $this->getChoices(),
                                                                'expanded'    => $this->getOptions()->get('expanded'),
                                                                'allow_clear' => $this->getOptions()->get('allow_clear'),
                                                                'attributes'  => ['multiple' => $this->getOptions()->get('multiple') == true]]));
    }
}