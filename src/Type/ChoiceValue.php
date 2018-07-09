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

class ChoiceValue
{
    /** @var string Label */
    private $label;
    /** @var string Value */
    private $value;
    /** @var mixed Final value */
    private $finalValue;
    /** @var array Attributes */
    private $attributes = [];
    /** @var bool Selected? */
    private $selected = false;

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set label.
     *
     * @param string $label
     *
     * @return static
     */
    public function setLabel(string $label): ChoiceValue
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return static
     */
    public function setValue(string $value): ChoiceValue
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get final value.
     *
     * @return mixed
     */
    public function getFinalValue()
    {
        return $this->finalValue;
    }

    /**
     * Set final value.
     *
     * @param mixed $value
     *
     * @return static
     */
    public function setFinalValue($value): ChoiceValue
    {
        $this->finalValue = $value;

        return $this;
    }

    /**
     * Get attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get attribute.
     *
     * @param string $name    Attribute name
     * @param null   $default Default value if not exists
     *
     * @return mixed|null
     */
    public function getAttribute(string $name, $default = null)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * Set attributes.
     *
     * @param array $attributes
     *
     * @return static
     */
    public function setAttributes(array $attributes): ChoiceValue
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Is selected?
     *
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * Set selected.
     *
     * @param bool $selected
     *
     * @return static
     */
    public function setSelected(bool $selected): ChoiceValue
    {
        $this->selected = $selected;

        return $this;
    }
}