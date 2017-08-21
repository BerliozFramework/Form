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
    /** @var string Type */
    private $type;
    /** @var string Label */
    private $label;
    /** @var string Value */
    private $value;
    /** @var string[] Attributes */
    private $attributes;
    /** @var bool Selected ? */
    private $selected;
    /** @var mixed Original data */
    private $originalData;

    /**
     * ChoiceValue constructor.
     */
    public function __construct()
    {
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

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
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
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
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * Get attributes.
     *
     * @return \string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set attributes.
     *
     * @param \string[] $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Is selected ?
     *
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected ?? false;
    }

    /**
     * Set selected.
     *
     * @param mixed $selected
     */
    public function setSelected(bool $selected)
    {
        $this->selected = $selected;
    }

    /**
     * Get original data.
     *
     * @return mixed
     */
    public function getOriginalData()
    {
        return $this->originalData;
    }

    /**
     * Set original data.
     *
     * @param mixed $originalData
     */
    public function setOriginalData($originalData)
    {
        $this->originalData = $originalData;
    }
}